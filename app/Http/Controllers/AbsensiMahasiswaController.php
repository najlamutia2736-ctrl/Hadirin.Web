<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Mahasiswa;
use App\Models\SesiAbsensi;
use App\Services\SesiAbsensiService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AbsensiMahasiswaController extends Controller
{
    /**
     * Sesi absensi dipakai hampir di setiap halaman, jadi diuru di constructor
     * supaya tidak di-resolve ulang setiap kali butuh.
     */
    public function __construct(protected SesiAbsensiService $sesiAbsensi) {}

    /**
     * Halaman awal absensi mahasiswa.
     *
     * Isinya dua hal: daftar jadwal kuliah hari ini beserta status presensi
     * per mata kuliah, lalu menu untuk memilih cara absen (scan QR, ID unik,
     * atau izin/sakit). Sesi tiap jadwal dibuat otomatis saat halaman dibuka,
     * jadi daftarnya tidak pernah menunggu proses lain. Semua angka rekap
     * diambil dari tabel `absensis` yang sama dengan dashboard dosen dan rekap
     * admin, jadi absensi yang tercatat di sini langsung muncul di kedua sisi.
     */
    public function index(Request $request): View
    {
        $mahasiswa = $request->user()?->mahasiswa;

        // Cuma akun mahasiswa yang punya halaman ini. Dosen dan admin yang salah
        // klik link dari navbar diarahkan keluar, bukan melihat data kosong.
        abort_unless($mahasiswa !== null, 403, 'Halaman ini hanya untuk akun mahasiswa.');

        return view('absensi.index', [
            'mahasiswa' => $mahasiswa,
            'hariIni' => $this->absensiHariIni($mahasiswa),
            'rekap' => $this->rekapBulanIni($mahasiswa),
            'presensiHariIni' => $this->presensiHariIni($mahasiswa),
        ]);
    }

    /**
     * Penjelasan semua cara absen.
     *
     * Halaman ini tidak mengirim angka rekap seperti `index`, karena yang
     * ditampilkan cuma daftar metode, langkahnya, dan status kehadiran hari
     * ini. Satu query untuk status itu sudah cukup.
     */
    public function metode(Request $request): View
    {
        $mahasiswa = $request->user()?->mahasiswa;

        // Guard sama dengan `index`: akun dosen atau admin yang salah klik
        // link-nya tidak boleh melihat halaman absensi mahasiswa.
        abort_unless($mahasiswa !== null, 403, 'Halaman ini hanya untuk akun mahasiswa.');

        $daftarSesi = $this->sesiHariIni($mahasiswa);

        return view('absensi.metode', [
            'mahasiswa' => $mahasiswa,
            'hariIni' => $this->absensiHariIni($mahasiswa),
            // Jumlah sesi hari ini yang belum punya catatan presensi. Yang
            // menentukan tombol "Presen Sekarang" adalah ini, bukan status
            // harian, karena satu mahasiswa bisa punya banyak sesi sehari.
            'sisaSesi' => $daftarSesi
                ->filter(fn (SesiAbsensi $sesi): bool => $this->absensiSesi($sesi, $mahasiswa) === null)
                ->count(),
        ]);
    }

    /**
     * Halaman absen lewat ID Unik.
     *
     * Dipakai saat kamera tidak bisa dipakai. Kode yang diketik harus milik mahasiswa
     * yang sedang login, jadi halaman ini tidak bisa dipakai untuk absen atas
     * nama orang lain. Mata kuliahnya dipilih dari sesi hari ini: bisa lewat
     * query `?sesi=` (tombol per jadwal di halaman utama), atau otomatis memakai
     * sesi yang sedang berjalan dan belum diabsen.
     */
    public function idUnik(Request $request): View
    {
        $mahasiswa = $request->user()?->mahasiswa;

        abort_unless($mahasiswa !== null, 403, 'Halaman ini hanya untuk akun mahasiswa.');

        $daftarSesi = $this->sesiHariIni($mahasiswa);
        $sesiTerpilih = $this->sesiDipilih($request, $daftarSesi);

        return view('absensi.id-unik', [
            'mahasiswa' => $mahasiswa,
            'hariIni' => $this->absensiHariIni($mahasiswa),
            'daftarSesi' => $daftarSesi,
            'sesiTerpilih' => $sesiTerpilih,
            'absensiSesi' => $this->absensiSesi($sesiTerpilih, $mahasiswa),
            'pilihanSesi' => $this->sesiBelumDiabsen($daftarSesi),
        ]);
    }

    /**
     * Catat kehadiran berdasarkan ID Unik yang diketik mahasiswa.
     *
     * Statusnya selalu `hadir`, sama seperti hasil pindai QR. Bedanya hanya cara
     * masuknya, dan itu memang tidak perlu disimpan terpisah karena yang dilihat
     * dosen di dashboard tetap sama: mahasiswa hadir di jam berapa dan pada
     * mata kuliah mana.
     */
    public function storeIdUnik(Request $request): RedirectResponse
    {
        $mahasiswa = $request->user()?->mahasiswa;

        abort_unless($mahasiswa !== null, 403, 'Halaman ini hanya untuk akun mahasiswa.');

        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:32'],
            'sesi' => ['required', 'integer', Rule::exists('sesi_absensis', 'id')],
        ]);

        $kode = $this->normalisasiKode($validated['kode']);

        /*
         | Kode dicek terhadap NIM mahasiswa yang sedang login, bukan dicari di
         | database. Kalau dicari, siapa pun bisa mengetik NIM temannya lalu
         | tercatat hadir untuk orang itu. Perbandingan di sini membuat kode
         | hanya berguna sebagai konfirmasi bahwa mahasiswa sendiri yang mengetik.
         */
        if (! hash_equals((string) $mahasiswa->nim, $kode)) {
            return redirect()
                ->route('absensi.id-unik')
                ->with('gagal', 'Kode tidak cocok dengan kartu presensimu.');
        }

        $sesi = $this->sesiUntukPresensi($mahasiswa, $validated['sesi']);

        if ($sesi === null) {
            return redirect()
                ->route('absensi.id-unik')
                ->with('gagal', 'Mata kuliah untuk sesi itu tidak dikenal atau bukan untuk kelasmu.');
        }

        if ($sesi->kondisiSekarang() === 'belum') {
            return redirect()
                ->route('absensi.id-unik')
                ->with('gagal', 'Sesi '.$sesi->judul().' belum dimulai (mulai '.$sesi->waktu_mulai->format('H:i').'), jadi kehadiran belum bisa dicatat.');
        }

        if ($sesi->kondisiSekarang() === 'selesai') {
            return redirect()
                ->route('absensi.id-unik')
                ->with('gagal', 'Sesi '.$sesi->judul().' sudah berakhir, jadi kehadiran tidak bisa dicatat lagi.');
        }

        // Satu mahasiswa satu catatan per sesi, jadi pengulangan untuk mata
        // kuliah yang sama ditolak, bukan diam-diam menimpa data lama. Beda
        // mata kuliah tetap boleh: itu justru presensi berikutnya hari ini.
        if ($this->absensiSesi($sesi, $mahasiswa) !== null) {
            return redirect()
                ->route('absensi.id-unik')
                ->with('gagal', 'Kehadiran untuk '.$sesi->judul().' sudah tercatat, jadi tidak perlu presen lagi.');
        }

        Absensi::create([
            'mahasiswa_id' => $mahasiswa->id,
            'sesi_absensi_id' => $sesi->id,
            'waktu_absen' => now(),
            'status' => 'hadir',
        ]);

        return redirect()
            ->route('absensi.id-unik')
            ->with('sukses', 'Kehadiran '.$sesi->judul().' tercatat pada '.now()->format('H:i').' WIB.');
    }

    /**
     * Riwayat absensi dan rekap untuk halaman notifikasi.
     *
     * Halaman ini hanya membaca, jadi tidak ada endpoint simpan. Isinya riwayat
     * absensi yang dipaginasi, rekap bulan berjalan, dan rekap 7 hari terakhir
     * supaya mahasiswa bisa lihat pola kehadiran tanpa membuka halaman lain.
     */
    public function notifikasi(Request $request): View
    {
        $mahasiswa = $request->user()?->mahasiswa;

        abort_unless($mahasiswa !== null, 403, 'Halaman ini hanya untuk akun mahasiswa.');

        return view('absensi.notifikasi', [
            'mahasiswa' => $mahasiswa,
            'hariIni' => $this->absensiHariIni($mahasiswa),
            'riwayat' => $this->riwayatAbsensi($mahasiswa),
            'rekap' => $this->rekapBulanIni($mahasiswa),
            'mingguan' => $this->rekapMingguIni($mahasiswa),
        ]);
    }

    /**
     * Halaman identitas mahasiswa.
     *
     * NIM dan kelas sengaja tidak bisa diubah dari sini. Keduanya menentukan
     * absensi: NIM dipakai sebagai kode ID Unik, dan kelas dipakai dosen untuk
     * memfilter mahasiswanya. Mengubahnya sendiri berarti mahasiswa bisa memindahkan
     * dirinya ke kelas lain atau mengubah kode absennya sendiri, jadi keduanya
     * milik admin yang salah ubah lewat dashboard CMS.
     */
    public function identitas(Request $request): View
    {
        $mahasiswa = $request->user()?->mahasiswa;

        abort_unless($mahasiswa !== null, 403, 'Halaman ini hanya untuk akun mahasiswa.');

        return view('absensi.identitas', [
            'mahasiswa' => $mahasiswa->load('user'),
            'rekap' => $this->rekapBulanIni($mahasiswa),
        ]);
    }

    /**
     * Simpan bagian identitas yang boleh diubah mahasiswa sendiri.
     *
     * Hanya nama, jenis kelamin, dan kontak orang tua. Squad Id Unik dan kelas
     * dikecualikan dengan sengaja, lihat {@see identitas()}.
     */
    public function updateIdentitas(Request $request): RedirectResponse
    {
        $mahasiswa = $request->user()?->mahasiswa;

        abort_unless($mahasiswa !== null, 403, 'Halaman ini hanya untuk akun mahasiswa.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'parent' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $mahasiswa->update([
            'jenis_kelamin' => $validated['gender'],
            'wali' => $validated['parent'] ?? null,
            'telepon_wali' => $validated['phone'] ?? null,
        ]);

        $mahasiswa->user?->update(['name' => $validated['name']]);

        return redirect()
            ->route('absensi.identitas')
            ->with('sukses', 'Identitas berhasil diperbarui.');
    }

    /**
     * Catatan absensi terbaru, dipaginasi.
     *
     * Dipaginasi supaya tabelnya tidak melebar kalau mahasiswa sudah presen selama
     * berbulan-bulan. Sesi di-eager load karena setiap baris menampilkannya, dan
     * jadwalnya ikut dibawa supaya riwayat menampilkan nama mata kuliah.
     *
     * @return LengthAwarePaginator<int, Absensi>
     */
    protected function riwayatAbsensi(Mahasiswa $mahasiswa): LengthAwarePaginator
    {
        return $mahasiswa->absensis()
            ->with($this->sesiBerpengikut())
            ->latest('waktu_absen')
            ->paginate(10)
            ->withQueryString();
    }

    /**
     * Kehadiran 7 hari terakhir, dihitung per hari.
     *
     * Hari tanpa catatan presensi ikut dikembalikan dengan status `null`,
     * supaya mahasiswa bisa melihat hari yang terlewat, bukan cuma hari yang punya
     * data. Kalau satu hari punya banyak catatan (per mata kuliah), yang dipakai
     * yang paling baru.
     *
     * @return array<int, array{tanggal: Carbon, status: string|null, waktu: string|null}>
     */
    protected function rekapMingguIni(Mahasiswa $mahasiswa): array
    {
        $dari = today()->subDays(6);

        // Dikelompokkan per tanggal supaya satu hari tidak terhitung berkali-kali
        // hanya karena mahasiswa punya banyak mata kuliah pada hari itu.
        $perTanggal = $mahasiswa->absensis()
            ->whereDate('waktu_absen', '>=', $dari)
            ->get(['waktu_absen', 'status'])
            ->groupBy(fn (Absensi $absen) => $absen->waktu_absen->toDateString())
            ->map(fn ($baris) => $baris->sortByDesc('waktu_absen')->first());

        $hasil = [];

        for ($hari = 0; $hari < 7; $hari++) {
            $tanggal = $dari->copy()->addDays($hari);
            $absen = $perTanggal->get($tanggal->toDateString());

            $hasil[] = [
                'tanggal' => $tanggal,
                'status' => $absen?->status,
                'waktu' => $absen?->waktu_absen?->format('H:i'),
            ];
        }

        return $hasil;
    }

    /**
     * Rapikan kode yang diketik supaya perbandingannya tidak gagal karena spasi
     * atau tanda hubung yang tidak disengaja.
     *
     * Spasi dan tanda hubung dibuang karena mahasiswa sering mengetik dengan format
     * bergaya sendiri, misalnya "2024 0101" atau "2024-0101".
     */
    protected function normalisasiKode(string $kode): string
    {
        return preg_replace('/[\s\-]+/', '', trim($kode)) ?? trim($kode);
    }

    /**
     * Formulir pengajuan izin atau sakit.
     *
     * Bedanya dari scan QR dan ID Unik, halaman ini tidak memakai kamera. Mata
     * kuliahnya dipilih dari jadwal hari ini supaya pengajuan masuk ke sesi yang
     * benar, dan yang ditampilkan selain formulir adalah catatan yang sudah
     * pernah dikirim, supaya mahasiswa tidak mengirim pengajuan yang sama dua
     * kali tanpa sadar.
     */
    public function izinSakit(Request $request): View
    {
        $mahasiswa = $request->user()?->mahasiswa;

        abort_unless($mahasiswa !== null, 403, 'Halaman ini hanya untuk akun mahasiswa.');

        $daftarSesi = $this->sesiHariIni($mahasiswa);
        $pilihanSesi = $this->sesiBelumDiabsen($daftarSesi)
            ->filter(fn (SesiAbsensi $sesi): bool => $sesi->kondisiSekarang() !== 'selesai')
            ->values();

        return view('absensi.izin-sakit', [
            'mahasiswa' => $mahasiswa,
            'hariIni' => $this->absensiHariIni($mahasiswa),
            'riwayat' => $this->riwayatIzinSakit($mahasiswa),
            'daftarSesi' => $daftarSesi,
            'pilihanSesi' => $pilihanSesi,
            'sesiTerpilih' => $this->sesiDipilih($request, $daftarSesi, $pilihanSesi),
            'semuaSudahTercatat' => $daftarSesi->isNotEmpty()
                && $daftarSesi->every(fn (SesiAbsensi $sesi): bool => $this->absensiSesi($sesi, $mahasiswa) !== null),
        ]);
    }

    /**
     * Simpan pengajuan izin atau sakit sebagai catatan presensi.
     *
     * Pengajuan dicatat di tabel yang sama dengan absensi biasa, dengan status
     * `izin` atau `sakit`, supaya langsung ikut terhitung di dashboard dosen dan
     * rekap admin tanpa langkah tambahan. Sesi absensinya dipilih dari jadwal
     * hari ini, jadi pengajuan hanya tercatat pada mata kuliah yang dituju.
     */
    public function storeIzinSakit(Request $request): RedirectResponse
    {
        $mahasiswa = $request->user()?->mahasiswa;

        abort_unless($mahasiswa !== null, 403, 'Halaman ini hanya untuk akun mahasiswa.');

        $validated = $request->validate([
            'jenis' => ['required', Rule::in(['izin', 'sakit'])],
            'alasan' => ['required', 'string', 'min:10', 'max:500'],
            'sesi' => ['required', 'integer', Rule::exists('sesi_absensis', 'id')],
        ]);

        $sesi = $this->sesiUntukPresensi($mahasiswa, $validated['sesi']);

        if ($sesi === null) {
            return redirect()
                ->route('absensi.izin-sakit')
                ->with('gagal', 'Mata kuliah untuk sesi itu tidak dikenal atau bukan untuk kelasmu.');
        }

        if ($sesi->kondisiSekarang() === 'selesai') {
            return redirect()
                ->route('absensi.izin-sakit')
                ->with('gagal', 'Sesi '.$sesi->judul().' sudah berakhir, jadi pengajuan tidak bisa dikirim lagi.');
        }

        // Satu catatan per sesi: pengajuan kedua untuk mata kuliah yang sama
        // ditolak, sementara mata kuliah lain hari ini tetap bisa diajukan.
        if ($this->absensiSesi($sesi, $mahasiswa) !== null) {
            return redirect()
                ->route('absensi.izin-sakit')
                ->with('gagal', 'Presensi '.$sesi->judul().' sudah tercatat, jadi tidak bisa kirim pengajuan lagi.');
        }

        Absensi::create([
            'mahasiswa_id' => $mahasiswa->id,
            'sesi_absensi_id' => $sesi->id,
            'waktu_absen' => now(),
            'status' => $validated['jenis'],
            'keterangan' => $validated['alasan'],
        ]);

        return redirect()
            ->route('absensi.izin-sakit')
            ->with('sukses', 'Pengajuan '.($validated['jenis'] === 'izin' ? 'izin' : 'sakit').' untuk '.$sesi->judul().' berhasil dikirim.');
    }

    /**
     * Relasi sesi yang dipakai daftar riwayat, lengkap dengan jadwalnya.
     *
     * @return array<string, array<int, string>>
     */
    protected function sesiBerpengikut(): array
    {
        return [
            'sesiAbsensi:id,jadwal_id,kode_sesi,waktu_mulai',
            'sesiAbsensi.jadwal:id,mata_kuliah',
        ];
    }

    /**
     * Sesi absensi yang sedang berjalan, atau null kalau belum ada.
     *
     * Pencariannya dipindah ke `SesiAbsensiService` supaya controller ini
     * tidak perlu tahu detail rentang waktunya.
     */
    protected function sesiBerjalan(): ?SesiAbsensi
    {
        return $this->sesiAbsensi->sesiBerjalan();
    }

    /**
     * Jadwal kuliah hari ini milik kelas mahasiswa ini.
     *
     * Sesi presensi dibuat dari jadwal inilah yang membuat presensi menjadi per
     * mata kuliah: satu baris jadwal, satu sesi, satu catatan absensi.
     *
     * @return Collection<int, Jadwal>
     */
    protected function jadwalHariIni(Mahasiswa $mahasiswa): Collection
    {
        return Jadwal::query()
            ->with('dosen.user:id,name')
            ->where('status', 'Aktif')
            ->where('hari', SesiAbsensiService::namaHari(today()))
            ->whereHas('kelas', fn ($query) => $query->where('nama_kelas', $mahasiswa->kelas))
            ->orderBy('jam_mulai')
            ->get();
    }

    /**
     * Sesi hari ini untuk kelas mahasiswa, lengkap dengan absensi miliknya.
     *
     * Sesi dibuat dulu (idempoten) supaya daftarnya selalu tersedia walau
     * scheduler di server tidak berjalan, lalu dimuat ulang lengkap dengan
     * jadwal dan satu baris absensi mahasiswa yang bersangkutan.
     *
     * @return Collection<int, SesiAbsensi>
     */
    protected function sesiHariIni(Mahasiswa $mahasiswa): Collection
    {
        $jadwal = $this->jadwalHariIni($mahasiswa);

        if ($jadwal->isEmpty()) {
            return new Collection;
        }

        $this->sesiAbsensi->pastikanSesi($jadwal, today());

        return SesiAbsensi::query()
            ->with('jadwal:id,mata_kuliah,ruang')
            ->with(['absensis' => fn ($query) => $query->where('mahasiswa_id', $mahasiswa->getKey())])
            ->whereIn('jadwal_id', $jadwal->pluck('id'))
            ->whereDate('waktu_mulai', today())
            ->orderBy('waktu_mulai')
            ->get();
    }

    /**
     * Sesi hari ini yang belum punya catatan presensi mahasiswa ini.
     *
     * @param  Collection<int, SesiAbsensi>  $daftarSesi
     * @return Collection<int, SesiAbsensi>
     */
    protected function sesiBelumDiabsen(Collection $daftarSesi): Collection
    {
        return $daftarSesi
            ->filter(fn (SesiAbsensi $sesi): bool => $sesi->absensis->isEmpty())
            ->values();
    }

    /**
     * Sesi yang dipilih dari query string, dengan fallback yang masuk akal.
     *
     * Urutan pilihannya: sesi yang diminta lewat `?sesi=` kalau masih sesi hari
     * ini milik kelasmu, lalu sesi yang sedang berjalan, lalu sesi mana pun
     * yang belum diabsen, terakhir sesi pertama hari ini supaya halaman tetap
     * punya konteks untuk ditampilkan.
     *
     * @param  Collection<int, SesiAbsensi>  $daftarSesi
     */
    protected function sesiDipilih(Request $request, Collection $daftarSesi, ?Collection $pilihanSesi = null): ?SesiAbsensi
    {
        if ($request->filled('sesi')) {
            $diminta = $daftarSesi->firstWhere('id', (int) $request->input('sesi'));

            if ($diminta !== null) {
                return $diminta;
            }
        }

        $pilihanSesi ??= $this->sesiBelumDiabsen($daftarSesi);

        return $pilihanSesi->first(fn (SesiAbsensi $sesi): bool => $sesi->kondisiSekarang() === 'berjalan')
            ?? $pilihanSesi->first()
            ?? $daftarSesi->first();
    }

    /**
     * Cari sesi berdasarkan id, tapi hanya kalau sesinya milik kelas mahasiswa.
     *
     * Pengecekan kelas lewat jadwalnya inilah yang mencegah mahasiswa memilih
     * sesi kelas lain — termasuk sesi lama yang sudah tidak punya jadwal.
     */
    protected function sesiUntukPresensi(Mahasiswa $mahasiswa, mixed $sesiId): ?SesiAbsensi
    {
        return SesiAbsensi::query()
            ->whereKey($sesiId)
            ->whereHas('jadwal', fn ($query) => $query
                ->whereHas('kelas', fn ($relasi) => $relasi->where('nama_kelas', $mahasiswa->kelas)))
            ->first();
    }

    /**
     * Catatan absensi mahasiswa untuk satu sesi, atau null kalau belum ada.
     */
    protected function absensiSesi(?SesiAbsensi $sesi, Mahasiswa $mahasiswa): ?Absensi
    {
        if ($sesi === null) {
            return null;
        }

        return $sesi->absensis->firstWhere('mahasiswa_id', $mahasiswa->getKey())
            ?? Absensi::query()
                ->where('sesi_absensi_id', $sesi->getKey())
                ->where('mahasiswa_id', $mahasiswa->getKey())
                ->first();
    }

    /**
     * Daftar jadwal hari ini beserta status presensi per mata kuliah.
     *
     * Setiap baris menjawab satu pertanyaan: mata kuliah apa, jam berapa, sesi
     * sudah dibuka atau belum, dan mahasiswa ini sudah absen atau belum.
     *
     * @return list<array{jadwal: Jadwal, sesi: SesiAbsensi|null, absensi: Absensi|null, kondisi: string, label: string}>
     */
    protected function presensiHariIni(Mahasiswa $mahasiswa): array
    {
        $jadwal = $this->jadwalHariIni($mahasiswa);

        if ($jadwal->isEmpty()) {
            return [];
        }

        $sesi = $this->sesiAbsensi->pastikanSesi($jadwal, today());
        $absensi = Absensi::query()
            ->where('mahasiswa_id', $mahasiswa->getKey())
            ->whereIn('sesi_absensi_id', $sesi->pluck('id'))
            ->get()
            ->keyBy('sesi_absensi_id');

        return $jadwal->map(function (Jadwal $jadwal) use ($sesi, $absensi): array {
            $sesiJadwal = $sesi->firstWhere('jadwal_id', $jadwal->getKey());
            $absen = $sesiJadwal === null ? null : $absensi->get($sesiJadwal->getKey());

            if ($absen !== null) {
                return [
                    'jadwal' => $jadwal,
                    'sesi' => $sesiJadwal,
                    'absensi' => $absen,
                    'kondisi' => 'sudah',
                    'label' => ucfirst($absen->status).' '.$absen->waktu_absen->format('H:i'),
                ];
            }

            if ($sesiJadwal === null) {
                return [
                    'jadwal' => $jadwal,
                    'sesi' => null,
                    'absensi' => null,
                    'kondisi' => 'belum',
                    'label' => 'Sesi belum dibuka',
                ];
            }

            $kondisi = $sesiJadwal->kondisiSekarang();

            return [
                'jadwal' => $jadwal,
                'sesi' => $sesiJadwal,
                'absensi' => null,
                'kondisi' => $kondisi,
                'label' => match ($kondisi) {
                    'berjalan' => 'Sedang berlangsung',
                    'selesai' => 'Terlewat',
                    default => 'Mulai '.$sesiJadwal->waktu_mulai->format('H:i'),
                },
            ];
        })->values()->all();
    }

    /**
     * Pengajuan izin atau sakit yang sudah pernah dikirim.
     *
     * Hanya 10 baris terbaru karena yang dicari mahasiswa biasanya cuma
     * pengajuan terakhir, bukan arsip lengkap.
     *
     * @return Collection<int, Absensi>
     */
    protected function riwayatIzinSakit(Mahasiswa $mahasiswa): Collection
    {
        return $mahasiswa->absensis()
            ->with($this->sesiBerpengikut())
            ->whereIn('status', ['izin', 'sakit'])
            ->latest('waktu_absen')
            ->limit(10)
            ->get();
    }

    /**
     * Catatan absensi terakhir mahasiswa hari ini, atau null kalau belum absen.
     *
     * Dengan presensi per mata kuliah bisa ada banyak catatan dalam sehari, jadi
     * yang diambil yang paling baru sebagai ringkasan status harian.
     */
    protected function absensiHariIni(Mahasiswa $mahasiswa): ?Absensi
    {
        return $mahasiswa->absensis()
            ->with($this->sesiBerpengikut())
            ->whereDate('waktu_absen', today())
            ->latest('waktu_absen')
            ->first();
    }

    /**
     * Rekap kehadiran bulan berjalan untuk mahasiswa ini.
     *
     * Dihitung dengan satu query agregat, bukan mengambil seluruh baris
     * absensi ke memori lalu dihitung di PHP. Satu baris = satu presensi per
     * mata kuliah, jadi angkanya memang menghitung catatan, bukan hari.
     *
     * @return array{hadir:int,izin:int,sakit:int,alpha:int,total:int,persentase:int}
     */
    protected function rekapBulanIni(Mahasiswa $mahasiswa): array
    {
        $dari = now()->startOfMonth();
        $sampai = $dari->copy()->endOfMonth()->endOfDay();

        $jumlah = $mahasiswa->absensis()
            ->whereBetween('waktu_absen', [$dari, $sampai])
            ->selectRaw('status, count(*) as jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $hadir = (int) ($jumlah['hadir'] ?? 0);
        $izin = (int) ($jumlah['izin'] ?? 0);
        $sakit = (int) ($jumlah['sakit'] ?? 0);
        $alpha = (int) ($jumlah['alpha'] ?? 0);
        $total = $hadir + $izin + $sakit + $alpha;

        return [
            'hadir' => $hadir,
            'izin' => $izin,
            'sakit' => $sakit,
            'alpha' => $alpha,
            'total' => $total,
            'persentase' => $total > 0 ? (int) round($hadir / $total * 100) : 0,
        ];
    }
}

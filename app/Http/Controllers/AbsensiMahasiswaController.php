<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\SesiAbsensi;
use App\Models\Mahasiswa;
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
     * Berisi dua hal: status kehadiran hari ini, lalu menu untuk memilih cara
     * absen (scan QR, ID unik, atau izin/sakit). Semua angka diambil dari tabel
     * `absensis` yang sama dengan dashboard dosen dan rekap admin, jadi absensi
     * yang tercatat di sini langsung muncul di kedua sisi tanpa langkah tambahan.
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

        return view('absensi.metode', [
            'mahasiswa' => $mahasiswa,
            'hariIni' => $this->absensiHariIni($mahasiswa),
        ]);
    }

    /**
     * Halaman absen lewat ID Unik.
     *
     * Dipakai saat kamera tidak bisa dipakai. Kode yang diketik harus milik mahasiswa
     * yang sedang login, jadi halaman ini tidak bisa dipakai untuk absen atas
     * nama orang lain.
     */
    public function idUnik(Request $request): View
    {
        $mahasiswa = $request->user()?->mahasiswa;

        abort_unless($mahasiswa !== null, 403, 'Halaman ini hanya untuk akun mahasiswa.');

        return view('absensi.id-unik', [
            'mahasiswa' => $mahasiswa,
            'hariIni' => $this->absensiHariIni($mahasiswa),
            'sesiBerjalan' => $this->sesiBerjalan(),
        ]);
    }

    /**
     * Catat kehadiran berdasarkan ID Unik yang diketik mahasiswa.
     *
     * Statusnya selalu `hadir`, sama seperti hasil pindai QR. Bedanya hanya cara
     * masuknya, dan itu memang tidak perlu disimpan terpisah karena yang dilihat
     * dosen di dashboard tetap sama: mahasiswa hadir di jam berapa.
     */
    public function storeIdUnik(Request $request): RedirectResponse
    {
        $mahasiswa = $request->user()?->mahasiswa;

        abort_unless($mahasiswa !== null, 403, 'Halaman ini hanya untuk akun mahasiswa.');

        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:32'],
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

        if ($this->absensiHariIni($mahasiswa) !== null) {
            return redirect()
                ->route('absensi.id-unik')
                ->with('gagal', 'Kehadiran hari ini sudah tercatat, jadi tidak perlu presen lagi.');
        }

        $sesi = $this->sesiBerjalan();

        if ($sesi === null) {
            return redirect()
                ->route('absensi.id-unik')
                ->with('gagal', 'Belum ada sesi presensi yang berjalan, jadi kehadiran belum bisa dicatat.');
        }

        Absensi::create([
            'mahasiswa_id' => $mahasiswa->id,
            'sesi_absensi_id' => $sesi->id,
            'waktu_absen' => now(),
            'status' => 'hadir',
        ]);

        return redirect()
            ->route('absensi.id-unik')
            ->with('sukses', 'Kehadiran berhasil dicatat pada '.now()->format('H:i').' WIB.');
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
     * berbulan-bulan. Sesi di-eager load karena setiap baris menampilkannya.
     *
     * @return LengthAwarePaginator<int, Absensi>
     */
    protected function riwayatAbsensi(Mahasiswa $mahasiswa): LengthAwarePaginator
    {
        return $mahasiswa->absensis()
            ->with('sesiAbsensi:id,kode_sesi')
            ->latest('waktu_absen')
            ->paginate(10)
            ->withQueryString();
    }

    /**
     * Kehadiran 7 hari terakhir, dihitung per hari.
     *
     * Hari tanpa catatan presensi ikut dikembalikan dengan status `null`,
     * supaya mahasiswa bisa melihat hari yang terlewat, bukan cuma hari yang punya
     * data. Kalau satu hari punya dua catatan, yang dipakai yang paling baru.
     *
     * @return array<int, array{tanggal: Carbon, status: string|null, waktu: string|null}>
     */
    protected function rekapMingguIni(Mahasiswa $mahasiswa): array
    {
        $dari = today()->subDays(6);

        // Dikelompokkan per tanggal supaya satu hari tidak terhitung dua kali
        // kalau ternyata ada lebih dari satu catatan.
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
     * Bedanya dari scan QR dan ID Unik, halaman ini tidak memakai kamera dan
     * tidak butuh sesi absensi yang sedang berjalan. Yang ditampilkan cuma
     * catatan yang sudah pernah dikirim, supaya mahasiswa tidak mengirim
     * pengajuan yang sama dua kali tanpa sadar.
     */
    public function izinSakit(Request $request): View
    {
        $mahasiswa = $request->user()?->mahasiswa;

        abort_unless($mahasiswa !== null, 403, 'Halaman ini hanya untuk akun mahasiswa.');

        return view('absensi.izin-sakit', [
            'mahasiswa' => $mahasiswa,
            'hariIni' => $this->absensiHariIni($mahasiswa),
            'riwayat' => $this->riwayatIzinSakit($mahasiswa),
        ]);
    }

    /**
     * Simpan pengajuan izin atau sakit sebagai catatan presensi.
     *
     * Pengajuan dicatat di tabel yang sama dengan absensi biasa, dengan status
     * `izin` atau `sakit`, supaya langsung ikut terhitung di dashboard dosen dan
     * rekap admin tanpa langkah tambahan. Sesi absensi diambil dari sesi yang
     * sedang berjalan, jadi kolomnya tidak pernah kosong.
     */
    public function storeIzinSakit(Request $request): RedirectResponse
    {
        $mahasiswa = $request->user()?->mahasiswa;

        abort_unless($mahasiswa !== null, 403, 'Halaman ini hanya untuk akun mahasiswa.');

        $validated = $request->validate([
            'jenis' => ['required', Rule::in(['izin', 'sakit'])],
            'alasan' => ['required', 'string', 'min:10', 'max:500'],
            'tanggal' => ['required', 'date', 'after_or_equal:today'],
        ]);

        // Absensi hanya boleh satu baris per hari, jadi pengajuan kedua di
        // hari yang sama ditolak di sini, bukan diam-diam menimpa data lama.
        if ($this->absensiHariIni($mahasiswa) !== null) {
            return redirect()
                ->route('absensi.izin-sakit')
                ->with('gagal', 'Kehadiran hari ini sudah tercatat, jadi tidak bisa kirim pengajuan lagi.');
        }

        $sesi = $this->sesiBerjalan();

        if ($sesi === null) {
            return redirect()
                ->route('absensi.izin-sakit')
                ->with('gagal', 'Belum ada sesi presensi yang berjalan, jadi pengajuan belum bisa disimpan.');
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
            ->with('sukses', 'Pengajuan '.($validated['jenis'] === 'izin' ? 'izin' : 'sakit').' berhasil dikirim.');
    }

    /**
     * Sesi absensi yang sedang berjalan, atau null kalau belum ada.
     *
     * Pencariannya dipindah ke `SesiAbsensiService` supaya controller ini
     * tidak perlu tahu detail rentang waktunya. Sesi dibuat otomatis oleh
     * command `sesi:absensi` yang dijadwalkan di `routes/console.php`.
     */
    protected function sesiBerjalan(): ?SesiAbsensi
    {
        return $this->sesiAbsensi->sesiBerjalan();
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
            ->with('sesiAbsensi:id,kode_sesi')
            ->whereIn('status', ['izin', 'sakit'])
            ->latest('waktu_absen')
            ->limit(10)
            ->get();
    }

    /**
     * Catatan absensi mahasiswa hari ini, atau null kalau belum absen.
     *
     * Absensi diambil paling akhir supaya kalau ada dua catatan di hari yang
     * sama, yang tampil adalah yang paling baru.
     */
    protected function absensiHariIni(Mahasiswa $mahasiswa): ?Absensi
    {
        return $mahasiswa->absensis()
            ->with('sesiAbsensi:id,kode_sesi,waktu_mulai')
            ->whereDate('waktu_absen', today())
            ->latest('waktu_absen')
            ->first();
    }

    /**
     * Rekap kehadiran bulan berjalan untuk mahasiswa ini.
     *
     * Dihitung dengan satu query agregat, bukan mengambil seluruh baris
     * absensi ke memori lalu dihitung di PHP.
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

<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\SesiAbsensi;
use App\Models\Siswa;
use App\Services\SesiAbsensiService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AbsensiSiswaController extends Controller
{
    /**
     * Sesi absensi dipakai hampir di setiap halaman, jadi diuru di constructor
     * supaya tidak di-resolve ulang setiap kali butuh.
     */
    public function __construct(protected SesiAbsensiService $sesiAbsensi) {}

    /**
     * Halaman awal absensi siswa.
     *
     * Berisi dua hal: status kehadiran hari ini, lalu menu untuk memilih cara
     * absen (scan QR, ID unik, atau izin/sakit). Semua angka diambil dari tabel
     * `absensis` yang sama dengan dashboard guru dan rekap admin, jadi absensi
     * yang tercatat di sini langsung muncul di kedua sisi tanpa langkah tambahan.
     */
    public function index(Request $request): View
    {
        $siswa = $request->user()?->siswa;

        // Cuma akun siswa yang punya halaman ini. Guru dan admin yang salah
        // klik link dari navbar diarahkan keluar, bukan melihat data kosong.
        abort_unless($siswa !== null, 403, 'Halaman ini hanya untuk akun siswa.');

        return view('absensi.index', [
            'siswa' => $siswa,
            'hariIni' => $this->absensiHariIni($siswa),
            'rekap' => $this->rekapBulanIni($siswa),
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
        $siswa = $request->user()?->siswa;

        // Guard sama dengan `index`: akun guru atau admin yang salah klik
        // link-nya tidak boleh melihat halaman absensi siswa.
        abort_unless($siswa !== null, 403, 'Halaman ini hanya untuk akun siswa.');

        return view('absensi.metode', [
            'siswa' => $siswa,
            'hariIni' => $this->absensiHariIni($siswa),
        ]);
    }

    /**
     * Halaman absen lewat ID Unik.
     *
     * Dipakai saat kamera tidak bisa dipakai. Kode yang diketik harus milik siswa
     * yang sedang login, jadi halaman ini tidak bisa dipakai untuk absen atas
     * nama orang lain.
     */
    public function idUnik(Request $request): View
    {
        $siswa = $request->user()?->siswa;

        abort_unless($siswa !== null, 403, 'Halaman ini hanya untuk akun siswa.');

        return view('absensi.id-unik', [
            'siswa' => $siswa,
            'hariIni' => $this->absensiHariIni($siswa),
            'sesiBerjalan' => $this->sesiBerjalan(),
        ]);
    }

    /**
     * Catat kehadiran berdasarkan ID Unik yang diketik siswa.
     *
     * Statusnya selalu `hadir`, sama seperti hasil pindai QR. Bedanya hanya cara
     * masuknya, dan itu memang tidak perlu disimpan terpisah karena yang dilihat
     * guru di dashboard tetap sama: siswa hadir di jam berapa.
     */
    public function storeIdUnik(Request $request): RedirectResponse
    {
        $siswa = $request->user()?->siswa;

        abort_unless($siswa !== null, 403, 'Halaman ini hanya untuk akun siswa.');

        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:32'],
        ]);

        $kode = $this->normalisasiKode($validated['kode']);

        /*
         | Kode dicek terhadap NISN siswa yang sedang login, bukan dicari di
         | database. Kalau dicari, siapa pun bisa mengetik NISN temannya lalu
         | tercatat hadir untuk orang itu. Perbandingan di sini membuat kode
         | hanya berguna sebagai konfirmasi bahwa siswa sendiri yang mengetik.
         */
        if (! hash_equals((string) $siswa->nisn, $kode)) {
            return redirect()
                ->route('absensi.id-unik')
                ->with('gagal', 'Kode tidak cocok dengan kartu absensimu.');
        }

        if ($this->absensiHariIni($siswa) !== null) {
            return redirect()
                ->route('absensi.id-unik')
                ->with('gagal', 'Kehadiran hari ini sudah tercatat, jadi tidak perlu absen lagi.');
        }

        $sesi = $this->sesiBerjalan();

        if ($sesi === null) {
            return redirect()
                ->route('absensi.id-unik')
                ->with('gagal', 'Belum ada sesi absensi yang berjalan, jadi kehadiran belum bisa dicatat.');
        }

        Absensi::create([
            'siswa_id' => $siswa->id,
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
     * supaya siswa bisa lihat pola kehadiran tanpa membuka halaman lain.
     */
    public function notifikasi(Request $request): View
    {
        $siswa = $request->user()?->siswa;

        abort_unless($siswa !== null, 403, 'Halaman ini hanya untuk akun siswa.');

        return view('absensi.notifikasi', [
            'siswa' => $siswa,
            'hariIni' => $this->absensiHariIni($siswa),
            'riwayat' => $this->riwayatAbsensi($siswa),
            'rekap' => $this->rekapBulanIni($siswa),
            'mingguan' => $this->rekapMingguIni($siswa),
        ]);
    }

    /**
     * Halaman identitas siswa.
     *
     * NISN dan kelas sengaja tidak bisa diubah dari sini. Keduanya menentukan
     * absensi: NISN dipakai sebagai kode ID Unik, dan kelas dipakai guru untuk
     * memfilter siswanya. Mengubahnya sendiri berarti siswa bisa memindahkan
     * dirinya ke kelas lain atau mengubah kode absennya sendiri, jadi keduanya
     * milik admin yang salah ubah lewat dashboard CMS.
     */
    public function identitas(Request $request): View
    {
        $siswa = $request->user()?->siswa;

        abort_unless($siswa !== null, 403, 'Halaman ini hanya untuk akun siswa.');

        return view('absensi.identitas', [
            'siswa' => $siswa->load('user'),
            'rekap' => $this->rekapBulanIni($siswa),
        ]);
    }

    /**
     * Simpan bagian identitas yang boleh diubah siswa sendiri.
     *
     * Hanya nama, jenis kelamin, dan kontak wali. Squad Id Unik dan kelas
     * dikecualikan dengan sengaja, lihat {@see identitas()}.
     */
    public function updateIdentitas(Request $request): RedirectResponse
    {
        $siswa = $request->user()?->siswa;

        abort_unless($siswa !== null, 403, 'Halaman ini hanya untuk akun siswa.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::in(['L', 'P'])],
            'parent' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $siswa->update([
            'jenis_kelamin' => $validated['gender'],
            'wali' => $validated['parent'] ?? null,
            'telepon_wali' => $validated['phone'] ?? null,
        ]);

        $siswa->user?->update(['name' => $validated['name']]);

        return redirect()
            ->route('absensi.identitas')
            ->with('sukses', 'Identitas berhasil diperbarui.');
    }

    /**
     * Catatan absensi terbaru, dipaginasi.
     *
     * Dipaginasi supaya tabelnya tidak melebar kalau siswa sudah absen selama
     * berbulan-bulan. Sesi di-eager load karena setiap baris menampilkannya.
     *
     * @return LengthAwarePaginator<int, Absensi>
     */
    protected function riwayatAbsensi(Siswa $siswa): LengthAwarePaginator
    {
        return $siswa->absensis()
            ->with('sesiAbsensi:id,kode_sesi')
            ->latest('waktu_absen')
            ->paginate(10)
            ->withQueryString();
    }

    /**
     * Kehadiran 7 hari terakhir, dihitung per hari.
     *
     * Hari tanpa catatan absensi ikut dikembalikan dengan status `null`,
     * supaya siswa bisa melihat hari yang terlewat, bukan cuma hari yang punya
     * data. Kalau satu hari punya dua catatan, yang dipakai yang paling baru.
     *
     * @return array<int, array{tanggal: Carbon, status: string|null, waktu: string|null}>
     */
    protected function rekapMingguIni(Siswa $siswa): array
    {
        $dari = today()->subDays(6);

        // Dikelompokkan per tanggal supaya satu hari tidak terhitung dua kali
        // kalau ternyata ada lebih dari satu catatan.
        $perTanggal = $siswa->absensis()
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
     * Spasi dan tanda hubung dibuang karena siswa sering mengetik dengan format
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
     * catatan yang sudah pernah dikirim, supaya siswa tidak mengirim
     * pengajuan yang sama dua kali tanpa sadar.
     */
    public function izinSakit(Request $request): View
    {
        $siswa = $request->user()?->siswa;

        abort_unless($siswa !== null, 403, 'Halaman ini hanya untuk akun siswa.');

        return view('absensi.izin-sakit', [
            'siswa' => $siswa,
            'hariIni' => $this->absensiHariIni($siswa),
            'riwayat' => $this->riwayatIzinSakit($siswa),
        ]);
    }

    /**
     * Simpan pengajuan izin atau sakit sebagai catatan absensi.
     *
     * Pengajuan dicatat di tabel yang sama dengan absensi biasa, dengan status
     * `izin` atau `sakit`, supaya langsung ikut terhitung di dashboard guru dan
     * rekap admin tanpa langkah tambahan. Sesi absensi diambil dari sesi yang
     * sedang berjalan, jadi kolomnya tidak pernah kosong.
     */
    public function storeIzinSakit(Request $request): RedirectResponse
    {
        $siswa = $request->user()?->siswa;

        abort_unless($siswa !== null, 403, 'Halaman ini hanya untuk akun siswa.');

        $validated = $request->validate([
            'jenis' => ['required', Rule::in(['izin', 'sakit'])],
            'alasan' => ['required', 'string', 'min:10', 'max:500'],
            'tanggal' => ['required', 'date', 'after_or_equal:today'],
        ]);

        // Absensi hanya boleh satu baris per hari, jadi pengajuan kedua di
        // hari yang sama ditolak di sini, bukan diam-diam menimpa data lama.
        if ($this->absensiHariIni($siswa) !== null) {
            return redirect()
                ->route('absensi.izin-sakit')
                ->with('gagal', 'Kehadiran hari ini sudah tercatat, jadi tidak bisa kirim pengajuan lagi.');
        }

        $sesi = $this->sesiBerjalan();

        if ($sesi === null) {
            return redirect()
                ->route('absensi.izin-sakit')
                ->with('gagal', 'Belum ada sesi absensi yang berjalan, jadi pengajuan belum bisa disimpan.');
        }

        Absensi::create([
            'siswa_id' => $siswa->id,
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
     * Hanya 10 baris terbaru karena yang dicari siswa biasanya cuma
     * pengajuan terakhir, bukan arsip lengkap.
     *
     * @return Collection<int, Absensi>
     */
    protected function riwayatIzinSakit(Siswa $siswa): Collection
    {
        return $siswa->absensis()
            ->with('sesiAbsensi:id,kode_sesi')
            ->whereIn('status', ['izin', 'sakit'])
            ->latest('waktu_absen')
            ->limit(10)
            ->get();
    }

    /**
     * Catatan absensi siswa hari ini, atau null kalau belum absen.
     *
     * Absensi diambil paling akhir supaya kalau ada dua catatan di hari yang
     * sama, yang tampil adalah yang paling baru.
     */
    protected function absensiHariIni(Siswa $siswa): ?Absensi
    {
        return $siswa->absensis()
            ->with('sesiAbsensi:id,kode_sesi,waktu_mulai')
            ->whereDate('waktu_absen', today())
            ->latest('waktu_absen')
            ->first();
    }

    /**
     * Rekap kehadiran bulan berjalan untuk siswa ini.
     *
     * Dihitung dengan satu query agregat, bukan mengambil seluruh baris
     * absensi ke memori lalu dihitung di PHP.
     *
     * @return array{hadir:int,izin:int,sakit:int,alpha:int,total:int,persentase:int}
     */
    protected function rekapBulanIni(Siswa $siswa): array
    {
        $dari = now()->startOfMonth();
        $sampai = $dari->copy()->endOfMonth()->endOfDay();

        $jumlah = $siswa->absensis()
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

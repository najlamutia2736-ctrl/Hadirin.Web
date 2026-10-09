<?php

namespace App\Services;

use App\Models\SesiAbsensi;
use Illuminate\Support\Carbon;

/**
 * Pembuat sesi absensi harian.
 *
 * Aplikasi ini tidak punya layar "buka sesi" untuk guru, padahal kolom
 * `sesi_absensi_id` di tabel `absensis` tidak boleh kosong. Tanpa proses yang
 * membuat sesi, siswa selalu kena pesan "Belum ada sesi absensi yang
 * berjalan" dan kehadiran tidak bisa dicatat sama sekali.
 *
 * Kelas ini mengisi celah itu: sesi untuk setiap hari dibuat otomatis dari
 * jam buka dan jam tutup di `config/sesi-absensi.php`, dan sesi yang
 * rentangnya sudah lewat ditutup supaya tidak lagi dianggap berjalan. Semua
 * operasi dibuat idempoten supaya aman dijalankan berkali-kali, baik dari
 * scheduler maupun manual.
 */
class SesiAbsensiService
{
    /**
     * Sesi yang sedang berjalan, atau null kalau belum ada.
     *
     * Sesi dicari dari rentang waktunya, bukan hanya dari kolom status,
     * karena sesi yang sudah lewat masih berstatus `aktif` kalau command
     * penutupnya belum sempat jalan.
     */
    public function sesiBerjalan(): ?SesiAbsensi
    {
        return SesiAbsensi::query()
            ->where('waktu_mulai', '<=', now())
            ->where('waktu_selesai', '>=', now())
            ->orderBy('waktu_mulai')
            ->first();
    }

    /**
     * Pastikan ada sesi yang sedang berjalan, buatkan kalau belum ada.
     *
     * Dipakai command harian supaya sesi hari ini selalu ada sebelum jam
     * sekolah mulai. Kalau ternyata masih ada sesi yang berjalan, sesi itu
     * dikembalikan tanpa membuat sesi baru, jadi command aman dijalankan
     * berulang kali.
     */
    public function pastikanSesiHariIni(): SesiAbsensi
    {
        return $this->sesiBerjalan() ?? $this->buatSesiHarian(today());
    }

    /**
     * Sesi harian untuk tanggal yang diberikan, memakai jam dari config.
     *
     * Sesi dimulai pada jam buka tanggal itu, bukan pada jam saat perintah
     * dijalankan, supaya sesi untuk hari esok sudah punya rentang waktu yang
     * benar sejak awal.
     */
    public function buatSesiHarian(Carbon $tanggal): SesiAbsensi
    {
        $buka = $this->waktuPadaJam($tanggal, config('sesi-absensi.jam_buka'));
        $tutup = $this->waktuPadaJam($tanggal, config('sesi-absensi.jam_tutup'));

        // Jam tutup yang tidak lebih besar dari jam buka berarti sesi melewati
        // tengah malam, jadi tanggal berakhirnya digeser satu hari.
        if ($tutup->lessThanOrEqualTo($buka)) {
            $tutup->addDay();
        }

        return $this->buatSesi($buka, $tutup);
    }

    /**
     * Simpan satu sesi dengan rentang waktu yang sudah ditentukan.
     *
     * Detik ikut dipangkas supaya `waktu_mulai` yang tampil di riwayat absensi
     * benar-benar sama dengan yang dipakai saat membandingkan sesi, bukan
     * menambah pecahan detik yang tidak pernah dilihat siapa pun.
     */
    public function buatSesi(Carbon $waktuMulai, ?Carbon $waktuSelesai = null, ?string $kodeSesi = null): SesiAbsensi
    {
        $waktuMulai = $waktuMulai->copy()->startOfSecond();
        $waktuSelesai = ($waktuSelesai ?? $waktuMulai->copy()->addHours(1))->copy()->startOfSecond();

        return SesiAbsensi::create([
            'kode_sesi' => $kodeSesi ?? $this->kodeSesi($waktuMulai),
            'waktu_mulai' => $waktuMulai,
            'waktu_selesai' => $waktuSelesai,
            'status' => 'aktif',
        ]);
    }

    /**
     * Tutup semua sesi yang rentangnya sudah lewat.
     *
     * Sesi basi yang masih berstatus `aktif` dirapikan supaya dashboard guru
     * tidak salah membaca sesi berjalan sebagai sesi yang sudah tutup.
     *
     * @return int jumlah sesi yang berubah status
     */
    public function tutupSesiLewat(): int
    {
        return SesiAbsensi::query()
            ->where('status', 'aktif')
            ->where('waktu_selesai', '<', now())
            ->update(['status' => 'selesai']);
    }

    /**
     * Sesi yang dimulai pada tanggal tertentu, atau null kalau belum ada.
     */
    public function sesiPadaTanggal(Carbon $tanggal): ?SesiAbsensi
    {
        return SesiAbsensi::query()
            ->whereDate('waktu_mulai', $tanggal->toDateString())
            ->first();
    }

    /**
     * Gabung tanggal dengan jam `HH:MM` dari config.
     *
     * Nilai config dibaca tiap pemanggilan, bukan disimpan di properti,
     * supaya pengujian bisa menimpa config sesaat lalu mengembalikannya tanpa
     * harus membuat objek baru.
     */
    protected function waktuPadaJam(Carbon $tanggal, ?string $jam): Carbon
    {
        return $tanggal->copy()
            ->setTimeFromTimeString(is_string($jam) && $jam !== '' ? $jam : '00:00');
    }

    /**
     * Kode sesi harian, misalnya `HARIAN-2026-10-09`.
     *
     * Tanggal ikut masuk ke kode supaya kodenya tetap unik dan mudah dibaca di
     * riwayat absensi, bukan sekadar `HARIAN` yang sama setiap hari.
     */
    protected function kodeSesi(Carbon $tanggal): string
    {
        $awalan = trim((string) config('sesi-absensi.kode_sesi'));

        return $awalan === ''
            ? 'SESI-'.$tanggal->format('Y-m-d')
            : $awalan.'-'.$tanggal->format('Y-m-d');
    }
}

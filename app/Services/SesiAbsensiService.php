<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\SesiAbsensi;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Pembuat sesi presensi per mata kuliah.
 *
 * Presensi di aplikasi ini dihitung per mata kuliah, bukan per hari: setiap
 * jadwal mengajar pada satu hari punya sesi sendiri, sehingga mahasiswa harus
 * absen ulang tiap kali jam pelajaran baru dimulai. Rentang waktu sesi
 * diambil langsung dari `jam_mulai`/`jam_selesai` di tabel `jadwals`, jadi
 * tidak ada jam buka-tutup global di config lagi.
 *
 * Sesi dibuat otomatis — baik oleh command `sesi:absensi` yang dijadwalkan di
 * `routes/console.php`, maupun oleh halaman yang membacanya — supaya mahasiswa
 * tidak pernah terkunci hanya karena scheduler di server tidak berjalan
 * (misalnya di hosting gratisan yang tidak punya cron). Semua operasi
 * idempoten: satu jadwal hanya menghasilkan satu sesi per tanggal, walau
 * dijalankan berkali-kali atau bersamaan.
 */
class SesiAbsensiService
{
    /**
     * Pemetaan nomor hari ISO-8601 (1 = Senin) ke nama hari yang dipakai
     * kolom `jadwals.hari`.
     *
     * @var array<int, string>
     */
    private const HARI = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    /**
     * Nama hari Indonesia untuk tanggal tertentu, sesuai isi kolom
     * `jadwals.hari` misalnya `Senin`.
     *
     * Dipakai juga oleh controller absensi supaya daftar jadwal hari ini dan
     * pembuatan sesi tidak pernah memakai nama hari yang berbeda.
     */
    public static function namaHari(Carbon $tanggal): string
    {
        return self::HARI[$tanggal->dayOfWeekIso];
    }

    /**
     * Pastikan sesi hari itu sudah ada untuk seluruh jadwal aktif, lalu tutup
     * sesi yang rentangnya sudah lewat.
     *
     * @return Collection<int, SesiAbsensi> sesi pada tanggal itu, terurut dari
     *                                      jam mulai paling awal
     */
    public function pastikanSesiHariIni(?Carbon $tanggal = null): Collection
    {
        $tanggal = ($tanggal ?? today())->copy();

        $jadwal = Jadwal::query()
            ->where('status', 'Aktif')
            // Jadwal tahun akademik lampau yang masih berstatus Aktif tidak
            // boleh membuat sesi presensi di tahun sekarang.
            ->where('tahun_akademik', $tanggal->year)
            ->where('hari', self::namaHari($tanggal))
            ->orderBy('jam_mulai')
            ->get();

        return $this->pastikanSesi($jadwal, $tanggal);
    }

    /**
     * Pastikan sesi sudah ada untuk sekumpulan jadwal pada tanggal tertentu.
     *
     * Dipakai halaman mahasiswa supaya hanya jadwal kelasnya sendiri yang
     * dibuat sesinya, bukan seluruh jadwal kampus.
     *
     * @param  Collection<int, Jadwal>  $jadwal
     * @return Collection<int, SesiAbsensi>
     */
    public function pastikanSesi(Collection $jadwal, Carbon $tanggal): Collection
    {
        $tanggal = $tanggal->copy();
        $sekarang = Carbon::now();

        if ($jadwal->isEmpty()) {
            $this->tutupSesiLewat();

            return new Collection;
        }

        $sudahAda = SesiAbsensi::query()
            ->whereIn('jadwal_id', $jadwal->pluck('id'))
            ->whereDate('waktu_mulai', $tanggal->toDateString())
            ->pluck('jadwal_id');

        $baris = $jadwal
            ->filter(fn (Jadwal $item): bool => ! $sudahAda->contains($item->getKey()))
            ->map(fn (Jadwal $item): array => $this->barisSesi($item, $tanggal))
            ->values()
            ->all();

        if ($baris !== []) {
            try {
                SesiAbsensi::query()->insert($baris);
            } catch (UniqueConstraintViolationException) {
                // Permintaan lain baru saja membuat sesi yang sama. Kunci unik
                // (jadwal_id, kode_sesi) yang menahannya, jadi di sini cukup
                // dianggap selesai tanpa menggandakan sesi.
            }
        }

        $this->tutupSesiLewat();

        return SesiAbsensi::query()
            ->whereIn('jadwal_id', $jadwal->pluck('id'))
            ->whereDate('waktu_mulai', $tanggal->toDateString())
            ->orderBy('waktu_mulai')
            ->get();
    }

    /**
     * Sesi yang sedang berjalan, atau null kalau belum ada.
     *
     * Pencarian memakai rentang waktunya, bukan kolom `status`, karena sesi
     * yang sudah lewat masih berstatus `aktif` kalau penutupnya belum sempat
     * jalan.
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
     * Tutup semua sesi yang rentangnya sudah lewat.
     *
     * Sesi basi yang masih `aktif` dirapikan supaya tampilan tidak salah
     * menyebut sesi kemarin sebagai sesi yang sedang berjalan.
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
     * Satu baris sesi untuk satu jadwal pada satu tanggal.
     *
     * Detik dipangkas supaya `waktu_mulai` identik dengan jam jadwalnya, dan
     * `kode_sesi` dibuat dari jadwal + tanggal supaya bisa dipakai sebagai
     * kunci unik saat pembuatan berjalan bersamaan.
     *
     * @return array<string, mixed>
     */
    protected function barisSesi(Jadwal $jadwal, Carbon $tanggal): array
    {
        $mulai = $tanggal->copy()->setTimeFromTimeString($jadwal->jam_mulai)->startOfSecond();
        $selesai = $tanggal->copy()->setTimeFromTimeString($jadwal->jam_selesai)->startOfSecond();

        // Jam selesai yang tidak lebih besar dari jam mulai berarti kelas
        // melewati tengah malam, jadi berakhirnya digeser satu hari.
        if ($selesai->lessThanOrEqualTo($mulai)) {
            $selesai->addDay();
        }

        $sekarang = Carbon::now();

        return [
            'jadwal_id' => $jadwal->getKey(),
            'kode_sesi' => $this->kodeSesi($jadwal, $tanggal),
            'waktu_mulai' => $mulai,
            'waktu_selesai' => $selesai,
            'status' => $selesai->lessThan($sekarang) ? 'selesai' : 'aktif',
            'created_at' => $sekarang,
            'updated_at' => $sekarang,
        ];
    }

    /**
     * Kode sesi, misalnya `JW12-2026-10-21`.
     *
     * ID jadwal ikut masuk supaya dua jadwal mata kuliah yang sama di kelas
     * berbeda tidak pernah berbagi kode.
     */
    protected function kodeSesi(Jadwal $jadwal, Carbon $tanggal): string
    {
        return 'JW'.$jadwal->getKey().'-'.$tanggal->format('Y-m-d');
    }
}

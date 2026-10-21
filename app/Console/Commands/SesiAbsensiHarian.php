<?php

namespace App\Console\Commands;

use App\Services\SesiAbsensiService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class SesiAbsensiHarian extends Command
{
    /**
     * Nama command yang dipanggil dari scheduler dan dari terminal.
     *
     * @var string
     */
    protected $signature = 'sesi:absensi
                            {--tanggal= : Tanggal sesi yang mau dibuat (YYYY-MM-DD), default hari ini}';

    /**
     * @var string
     */
    protected $description = 'Buat sesi presensi per mata kuliah dan tutup sesi yang sudah lewat';

    /**
     * Jalankan pembuatan sesi.
     *
     * Sesi dibuat dari jadwal mengajar tanggal yang diminta: satu mata kuliah
     * satu sesi, dengan jam mulai dan selesai miliknya sendiri. Sesi yang
     * rentangnya sudah lewat ikut ditutup, dan karena pembuatannya idempoten,
     * command ini aman dijalankan berulang kali maupun bersamaan dengan
     * scheduler.
     */
    public function handle(SesiAbsensiService $sesi): int
    {
        $tanggal = $this->tanggalDipilih();

        if ($tanggal === null) {
            $this->components->error('Format tanggal tidak valid. Gunakan YYYY-MM-DD.');

            return self::FAILURE;
        }

        $daftar = $sesi->pastikanSesiHariIni($tanggal);

        $this->components->twoColumnDetail(
            'Sesi dibuat',
            (string) $daftar->count()
        );

        foreach ($daftar->take(10) as $sesiHariIni) {
            $this->components->twoColumnDetail(
                $sesiHariIni->judul(),
                $sesiHariIni->waktu_mulai->format('d M Y H:i').' - '.$sesiHariIni->waktu_selesai->format('d M Y H:i')
            );
        }

        if ($daftar->count() > 10) {
            $this->components->twoColumnDetail(
                'Sesi lainnya',
                (string) ($daftar->count() - 10)
            );
        }

        if ($daftar->isEmpty()) {
            $this->components->warn('Tidak ada jadwal kuliah aktif pada tanggal itu.');
        }

        return self::SUCCESS;
    }

    /**
     * Tanggal dari opsi `--tanggal`, atau hari ini kalau opsinya kosong.
     *
     * Tanggal dibersihkan ke awal hari supaya perbandingan dengan
     * `whereDate` di service tidak bergantung pada jam saat perintah dijalankan.
     */
    protected function tanggalDipilih(): ?Carbon
    {
        $tanggal = $this->option('tanggal');

        if (! is_string($tanggal) || trim($tanggal) === '') {
            return today();
        }

        try {
            return Carbon::createFromFormat('Y-m-d', trim($tanggal))->startOfDay();
        } catch (InvalidArgumentException) {
            return null;
        }
    }
}

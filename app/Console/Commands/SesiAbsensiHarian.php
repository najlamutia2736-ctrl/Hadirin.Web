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
    protected $description = 'Buat sesi absensi harian dan tutup sesi yang sudah lewat';

    /**
     * Jalankan pembuatan sesi.
     *
     * Dua pekerjaan yang selalu berpasangan: sesi yang rentangnya sudah lewat
     * ditutup lebih dulu, lalu sesi untuk tanggal yang diminta dibuat kalau
     * belum ada. Sesi yang sama tidak pernah dibuat dua kali, jadi command ini
     * aman dijalankan berulang kali maupun bersamaan dengan scheduler.
     */
    public function handle(SesiAbsensiService $sesi): int
    {
        $ditutup = $sesi->tutupSesiLewat();

        $tanggal = $this->tanggalDipilih();

        if ($tanggal === null) {
            $this->components->error('Format tanggal tidak valid. Gunakan YYYY-MM-DD.');

            return self::FAILURE;
        }

        $sudahAda = $sesi->sesiPadaTanggal($tanggal);

        $hasil = $sudahAda ?? $sesi->buatSesiHarian($tanggal);

        $this->components->twoColumnDetail(
            $sudahAda === null ? 'Sesi dibuat' : 'Sesi sudah ada',
            $hasil->kode_sesi
        );

        $this->components->twoColumnDetail(
            'Waktu',
            $hasil->waktu_mulai->format('d M Y H:i').' - '.$hasil->waktu_selesai->format('d M Y H:i')
        );

        $this->components->twoColumnDetail('Sesi ditutup', (string) $ditutup);

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

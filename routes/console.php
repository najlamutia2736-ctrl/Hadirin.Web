<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
| Sesi presensi dibuat otomatis per mata kuliah: `sesi:absensi` membuat sesi
| untuk semua jadwal mengajar hari ini (satu sesi per mata kuliah) lalu
| menutup sesi yang jam selesainya sudah lewat. Idempoten, jadi aman dijalankan
| berulang kali.
|
| Dijadwalkan tiap lima menit supaya sesi baru langsung ada begitu jam
| pelajaran dimulai. Halaman absensi mahasiswa dan dashboard dosen juga membuat
| sesi yang mereka butuhkan saat dibuka, sehingga presensi tetap jalan walau
| server tempat aplikasi ini berjalan tidak menjalankan scheduler sama sekali.
*/
Schedule::command('sesi:absensi')
    ->everyFiveMinutes()
    ->name('sesi-absensi-per-jadwal');

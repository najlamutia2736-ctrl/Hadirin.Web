<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
| Sesi absensi dibuat otomatis supaya siswa tidak pernah gagal absen hanya
| karena tidak ada guru yang kebetulan membuka sesi. Command `sesi:absensi`
| menutup sesi yang sudah lewat lalu membuat sesi untuk hari ini, dan aman
| dijalankan berulang kali.
|
| Dijadwalkan dua kali: pagi sebelum jam buka supaya sesi sudah siap saat
| siswa pertama masuk, dan sore setelah jam tutup supaya sesi yang hari ini
| ditandai selesai. Jalankan dua kali sehari lewat `php artisan schedule:run`.
*/
Schedule::command('sesi:absensi')->dailyAt('05:00')->name('sesi-absensi-buka');
Schedule::command('sesi:absensi')->dailyAt('21:30')->name('sesi-absensi-tutup');

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Jadwal Sesi Absensi Harian
    |--------------------------------------------------------------------------
    |
    | Satu sesi absensi dibuat untuk setiap hari. Sesi inilah yang dipakai saat
    | mahasiswa absen lewat Kode NIM atau mengirim pengajuan izin/sakit, dan sesi
    | ini juga yang dibaca dashboard dosen saat menghitung rekap.
    |
    | Sesi dibuat otomatis oleh command `php artisan sesi:absensi` yang
    | dijadwalkan di `routes/console.php`, jadi tidak perlu ada dosen yang
    | menekan tombol dulu. Command-nya idempoten: dijalankan dua kali di hari
    | yang sama hanya menghasilkan satu sesi.
    |
    | `jam_buka` dan `jam_tutup` memakai format `HH:MM` (24 jam) pada timezone
    | aplikasi, yaitu waktu kampus berlangsung. Kalau `jam_tutup` lebih kecil
    | dari `jam_buka`, sesi dianggap melewati tengah malam dan berakhir pada
    | hari berikutnya.
    |
    | `kode_sesi` adalah awalan kode sesi harian. Kodenya digabung dengan
    | tanggal, misalnya `HARIAN-2026-10-09`, supaya unik dan mudah dibaca di
    | riwayat absensi.
    |
    */

    'jam_buka' => '06:00',

    'jam_tutup' => '21:00',

    'kode_sesi' => 'HARIAN',

];

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Metode Absensi Siswa
    |--------------------------------------------------------------------------
    |
    | Daftar cara absen yang ditampilkan oleh dua halaman sekaligus:
    | `absensi/metode` (halaman khusus) dan halaman depan `/` (dipakai sebagai
    | panduan untuk siswa yang mau absen).
    |
    | Disimpan di config, bukan ditulis di tiap view, karena dua halaman itu
    | harus menampilkan cara absen yang sama persis. Kalau daftarnya ditulis dua
    | kali, pasti ada saat keduanya sudah berbeda. `config/menu.php` memakai
    | pola yang sama untuk data navigasi.
    |
    | `route` sengaja menyimpan NAMA route, bukan URL jadi. Halaman depan bisa
    | dibuka siapa saja, termasuk tamu, dan tidak boleh memuat URL halaman
    | absensi (lihat `MenuAbsensiTest`). Dengan nama route, halaman depan bisa
    | menampilkan penjelasannya tanpa perlu memanggil `route()` sama sekali.
    |
    | Struktur tiap metode:
    |   'label'     => string  -> judul metode
    |   'ringkasan' => string  -> satu kalimat singkat untuk kartu ringkas
    |   'penjelasan'=> string  -> paragraf untuk halaman metode
    |   'ikon'      => string  -> class ikon Font Awesome
    |   'warna'     => string  -> warna latar ikon
    |   'accent'    => string  -> class gradien untuk garis atas kartu
    |   'route'     => string  -> nama route halaman tujuan
    |   'butuh'     => string  -> syarat singkat
    |   'langkah'   => array   -> daftar langkah bernomor
    |
    */

    [
        'label' => 'Scan QR Code',
        'ringkasan' => 'Arahkan kamera ke QR code yang terpasang di kelas.',
        'penjelasan' => 'Cara paling cepat. Kamera HP membaca QR code di dinding, lalu kehadiranmu langsung tercatat tanpa perlu mengetik apa pun.',
        'ikon' => 'fa-qrcode',
        'warna' => 'bg-indigo-50 text-indigo-600',
        'accent' => 'from-indigo-500 to-blue-500',
        'route' => 'absensi.scan-qr',
        'butuh' => 'Kamera + izin akses kamera',
        'langkah' => [
            'Buka halaman Scan QR Code dari daftar di atas.',
            'Izinkan browser memakai kamera saat diminta.',
            'Arahkan bingkai ke QR code sampai terbaca otomatis.',
        ],
    ],

    [
        'label' => 'Kode NISN',
        'ringkasan' => 'Ketik NISN pribadi yang tertera di kartu.',
        'penjelasan' => 'Dipakai kalau kamera tidak bisa dipakai, misalnya HP tidak punya kamera atau sedang dipakai aplikasi lain.',
        'ikon' => 'fa-keyboard',
        'warna' => 'bg-emerald-50 text-emerald-600',
        'accent' => 'from-emerald-500 to-teal-500',
        'route' => 'absensi.id-unik',
        'butuh' => 'NISN dari kartu siswa',
        'langkah' => [
            'Buka halaman Kode NISN dari daftar di atas.',
            'Ketik NISN yang tertera di kartu absensi.',
            'Kirim dan tunggu konfirmasi masuk.',
        ],
    ],

    [
        'label' => 'Izin / Sakit',
        'ringkasan' => 'Kirim keterangan kalau tidak bisa hadir di sekolah.',
        'penjelasan' => 'Berbeda dari dua cara di atas, ini bukan kehadiran. Izin dan sakit dicatat supaya tidak dihitung sebagai alpa.',
        'ikon' => 'fa-envelope-open-text',
        'warna' => 'bg-amber-50 text-amber-600',
        'accent' => 'from-amber-500 to-orange-500',
        'route' => 'absensi.izin-sakit',
        'butuh' => 'Alasan yang jelas',
        'langkah' => [
            'Buka halaman Izin / Sakit dari daftar di atas.',
            'Pilih jenis pengajuan dan tulis alasannya.',
            'Kirim pengajuan ke wali kelas.',
        ],
    ],

    [
        'label' => 'Notifikasi',
        'ringkasan' => 'Lihat riwayat dan hasil absensi yang sudah dikirim.',
        'penjelasan' => 'Hanya untuk melihat. Di sini kamu bisa cek apakah absensi hari ini sudah tercatat atau masih diproses.',
        'ikon' => 'fa-bell',
        'warna' => 'bg-sky-50 text-sky-600',
        'accent' => 'from-sky-500 to-cyan-500',
        'route' => 'absensi.notifikasi',
        'butuh' => 'Tidak ada',
        'langkah' => [
            'Buka halaman Notifikasi dari daftar di atas.',
            'Lihat daftar kehadiran terbaru.',
            'Cek status absensi hari ini.',
        ],
    ],
];

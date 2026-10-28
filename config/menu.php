<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Menu Sidebar
    |--------------------------------------------------------------------------
    |
    | Definisi menu untuk `components.sidebar`. Dipilih otomatis oleh
    | View Composer di AppServiceProvider berdasarkan prefix nama route
    | (mis. route `dosen.dashboard` -> menu `dosen`).
    |
    | Struktur tiap grup:
    |   'label' => string  -> judul grup
    |   'items' => array   => daftar link
    |     'label'  => string  -> teks menu
    |     'route'  => string  -> nama route
    |     'icon'   => string  -> class ikon Font Awesome
    |     'match'  => string  -> (opsional) prefix route lain yg ikut aktif
    |
    */

    'cms' => [
        [
            'label' => 'Menu Utama',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'cms.dashboard', 'icon' => 'fas fa-tachometer-alt'],
                ['label' => 'Pindai Barcode', 'route' => 'cms.pindai', 'icon' => 'fas fa-barcode'],
            ],
        ],
        [
            'label' => 'Manajemen',
            'items' => [
                ['label' => 'Mahasiswa', 'route' => 'cms.mahasiswa', 'icon' => 'fas fa-user-graduate'],
                ['label' => 'Dosen', 'route' => 'cms.dosens', 'icon' => 'fas fa-chalkboard-user'],
                ['label' => 'Kelas', 'route' => 'cms.kelas', 'icon' => 'fas fa-book-open'],
                ['label' => 'Mata Kuliah', 'route' => 'cms.mata-kuliah', 'icon' => 'fas fa-layer-group'],
                ['label' => 'Jadwal', 'route' => 'cms.jadwal', 'icon' => 'fas fa-calendar-days'],
            ],
        ],
        [
            'label' => 'Sistem',
            'items' => [
                ['label' => 'Pengguna', 'route' => 'cms.users', 'icon' => 'fas fa-users-cog'],
                ['label' => 'Rekap Absensi', 'route' => 'cms.rekap', 'icon' => 'fas fa-clipboard-list'],
            ],
        ],
        [
            'label' => 'Akun',
            'items' => [
                ['label' => 'Kembali ke Beranda', 'route' => 'beranda', 'icon' => 'fas fa-arrow-left'],
            ],
        ],
    ],

    'dosen' => [
        [
            'label' => 'Menu Utama',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'dosen.dashboard', 'icon' => 'fas fa-tachometer-alt'],
                ['label' => 'Pindai Barcode', 'route' => 'dosen.pindai', 'icon' => 'fas fa-barcode'],
                ['label' => 'Kelola Data Presensi', 'route' => 'dosen.kelola', 'icon' => 'fas fa-users-cog'],
            ],
        ],
        [
            'label' => 'Laporan',
            'items' => [
                ['label' => 'Laporan Presensi', 'route' => 'dosen.laporan', 'icon' => 'fas fa-file-alt'],
            ],
        ],
        [
            'label' => 'Akun',
            'items' => [
                ['label' => 'Kembali ke Beranda', 'route' => 'beranda', 'icon' => 'fas fa-arrow-left'],
            ],
        ],
    ],

    /*
    | Identitas pengguna yang tampil di footer sidebar.
    |
    | Nilai di bawah hanya dipakai kalau belum ada user yang login. Kalau
    | sudah login, nama & email diambil dari `Auth::user()` oleh View Composer
    | di AppServiceProvider.
    */
    'user' => [
        'cms' => [
            'initial' => 'AD',
            'name' => 'Admin',
            'email' => 'admin@kampus.ac.id',
        ],
        'dosen' => [
            'initial' => 'D',
            'name' => 'Dosen',
            'email' => '-',
        ],
    ],

    /*
    | Subtitle brand di header sidebar, mengikuti area route yang dibuka.
    */
    'brand' => [
        'cms' => 'Manajemen Kampus',
        'dosen' => 'Portal Dosen',
    ],

    /*
    | Halaman yang memakai layout sidebar (prefix route).
    */
    'areas' => [
        'cms' => 'cms.*',
        'dosen' => 'dosen.*',
    ],

];

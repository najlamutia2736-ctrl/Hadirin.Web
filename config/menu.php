<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Menu Sidebar
    |--------------------------------------------------------------------------
    |
    | Definisi menu untuk `components.sidebar`. Dipilih otomatis oleh
    | View Composer di AppServiceProvider berdasarkan prefix nama route
    | (mis. route `guru.dashboard` -> menu `guru`).
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
            ],
        ],
        [
            'label' => 'Manajemen',
            'items' => [
                ['label' => 'Students', 'route' => 'cms.student', 'icon' => 'fas fa-user-graduate'],
                ['label' => 'Teachers', 'route' => 'cms.teachers', 'icon' => 'fas fa-chalkboard-teacher'],
                ['label' => 'Classes', 'route' => 'cms.classes', 'icon' => 'fas fa-book-open'],
                ['label' => 'Jadwal', 'route' => 'cms.jadwal', 'icon' => 'fas fa-calendar-days'],
            ],
        ],
        [
            'label' => 'Sistem',
            'items' => [
                ['label' => 'Users', 'route' => 'cms.users', 'icon' => 'fas fa-users-cog'],
                ['label' => 'Rekap', 'route' => 'cms.rekap', 'icon' => 'fas fa-clipboard-list'],
            ],
        ],
        [
            'label' => 'Akun',
            'items' => [
                ['label' => 'Kembali ke Beranda', 'route' => 'beranda', 'icon' => 'fas fa-arrow-left'],
            ],
        ],
    ],

    'guru' => [
        [
            'label' => 'Menu Utama',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'guru.dashboard', 'icon' => 'fas fa-tachometer-alt'],
                ['label' => 'Progres Absensi', 'route' => 'guru.progres', 'icon' => 'fas fa-chart-line'],
                ['label' => 'Kelola Data Kelas', 'route' => 'guru.kelola', 'icon' => 'fas fa-users-cog'],
            ],
        ],
        [
            'label' => 'Laporan',
            'items' => [
                ['label' => 'Laporan Bulanan', 'route' => 'guru.laporan', 'icon' => 'fas fa-file-alt'],
                ['label' => 'Real-Time Monitoring', 'route' => 'guru.realtime', 'icon' => 'fas fa-clock'],
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
            'email' => 'admin@sekolah.id',
        ],
        'guru' => [
            'initial' => 'G',
            'name' => 'Guru',
            'email' => '-',
        ],
    ],

    /*
    | Subtitle brand di header sidebar, mengikuti area route yang dibuka.
    */
    'brand' => [
        'cms' => 'School Management',
        'guru' => 'Portal Guru',
    ],

    /*
    | Halaman yang memakai layout sidebar (prefix route).
    */
    'areas' => [
        'cms' => 'cms.*',
        'guru' => 'guru.*',
    ],

];

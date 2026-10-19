<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('tiga halaman dashboard dosen dapat diakses dan memakai layout sidebar', function () {
    // Rekap bulanan dan progres absensi sudah digabung, lalu real-time
    // monitoring menyusul ke halaman dashboard. Sisa halaman dosen: dashboard,
    // kelola, dan laporan.
    $halaman = [
        'dosen.dashboard' => 'Dashboard',
        'dosen.kelola' => 'Kelola Data Kelas',
        'dosen.laporan' => 'Laporan Bulanan',
    ];

    loginDosen();

    foreach ($halaman as $route => $judul) {
        $this->get(route($route))
            ->assertOk()
            ->assertSee('Hadirin.Web')
            ->assertSee($judul)
            ->assertSee('Kembali ke Beranda')
            ->assertDontSee('Ubah Identitas');
    }
});

test('sidebar dosen menampilkan menu area dosen dan menandai halaman aktif', function () {
    loginDosen();

    $this->get(route('dosen.laporan'))
        ->assertOk()
        ->assertSee(route('dosen.dashboard'), false)
        ->assertSee('Kembali ke Beranda')
        // Menu CMS tidak boleh bocor ke halaman dosen
        ->assertDontSee(route('cms.users'), false);
});

test('sidebar cms tetap memakai menu cms dan tidak menampilkan menu dosen', function () {
    loginAdmin();

    $this->get(route('cms.dashboard'))
        ->assertOk()
        ->assertSee(route('cms.users'), false)
        ->assertSee(route('cms.rekap'), false)
        ->assertDontSee(route('dosen.laporan'), false);
});

test('semua halaman cms punya link kembali ke beranda', function () {
    $halaman = [
        'cms.dashboard' => 'Dashboard',
        'cms.mahasiswa' => 'Mahasiswa',
        'cms.dosens' => 'Dosen',
        'cms.kelas' => 'Kelas',
        'cms.jadwal' => 'Jadwal',
        'cms.users' => 'Pengguna',
        'cms.rekap' => 'Rekap',
    ];

    foreach ($halaman as $route => $judul) {
        loginAdmin();
        $this->get(route($route))
            ->assertOk()
            ->assertSee($judul)
            ->assertSee('Kembali ke Beranda')
            ->assertSee(route('beranda'), false);
    }
});

test('halaman dosen memuat data layer dan script yang dibutuhkan', function () {
    loginDosen();

    // Dashboard sudah absorbing fitur real-time monitoring, jadi polling dan
    // data layer keduanya harus ada di satu halaman ini.
    $this->get(route('dosen.dashboard'))
        ->assertOk()
        ->assertSee('cdn.jsdelivr.net/npm/chart.js', false)
        ->assertSee('initHalamanDosen', false)
        ->assertSee('loadRealtimeFromStorage', false)
        ->assertSee('startRealtimeAutoRefresh', false);
});

test('halaman dosen tidak mengarahkan ke form identitas dosen', function () {
    // Identitas dosen dibaca dari database lewat `window.HADIRIN_DOSEN`. Kalau
    // dosen belum punya kelas, halaman harus tetap tampil dengan kondisi
    // kosong, bukan dialihkan ke form identitas.
    $halaman = ['dosen.dashboard', 'dosen.kelola', 'dosen.laporan'];

    loginDosen();

    foreach ($halaman as $route) {
        $this->get(route($route))
            ->assertOk()
            ->assertSee('IDENTITAS_KOSONG', false)
            ->assertDontSee('/identitas-dosen', false);
    }
});

test('layout sidebar menyediakan stack styles dan scripts', function () {
    loginDosen();

    $this->get(route('dosen.kelola'))
        ->assertOk()
        ->assertSee('.stat-card', false)
        ->assertSee('tambahMahasiswa', false)
        ->assertSee('hapusMahasiswa', false);
});

test('alamat lama real-time monitoring mengarah ke dashboard dosen', function () {
    loginDosen();

    $this->get('/dashboard/dosen/realtime')
        ->assertRedirect(route('dosen.dashboard'));
});

<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('empat halaman dashboard guru dapat diakses dan memakai layout sidebar', function () {
    // Rekap bulanan dan progres absensi sudah digabung, jadi halaman guru
    // tinggal empat: dashboard, kelola, laporan, dan real-time.
    $halaman = [
        'guru.dashboard' => 'Dashboard',
        'guru.kelola' => 'Kelola Data Kelas',
        'guru.laporan' => 'Laporan Bulanan',
        'guru.realtime' => 'Real-Time Monitoring',
    ];

    loginGuru();

    foreach ($halaman as $route => $judul) {
        $this->get(route($route))
            ->assertOk()
            ->assertSee('Hadirin.Web')
            ->assertSee($judul)
            ->assertSee('Kembali ke Beranda')
            ->assertDontSee('Ubah Identitas');
    }
});

test('sidebar guru menampilkan menu area guru dan menandai halaman aktif', function () {
    loginGuru();

    $this->get(route('guru.laporan'))
        ->assertOk()
        ->assertSee(route('guru.dashboard'), false)
        ->assertSee(route('guru.realtime'), false)
        ->assertSee('Kembali ke Beranda')
        // Menu CMS tidak boleh bocor ke halaman guru
        ->assertDontSee(route('cms.users'), false);
});

test('sidebar cms tetap memakai menu cms dan tidak menampilkan menu guru', function () {
    loginAdmin();

    $this->get(route('cms.dashboard'))
        ->assertOk()
        ->assertSee(route('cms.users'), false)
        ->assertSee(route('cms.rekap'), false)
        ->assertDontSee(route('guru.laporan'), false);
});

test('semua halaman cms punya link kembali ke beranda', function () {
    $halaman = [
        'cms.dashboard' => 'Dashboard',
        'cms.student' => 'Students',
        'cms.teachers' => 'Teachers',
        'cms.classes' => 'Classes',
        'cms.jadwal' => 'Jadwal',
        'cms.users' => 'Users',
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

test('halaman guru memuat data layer dan script yang dibutuhkan', function () {
    loginGuru();

    $this->get(route('guru.dashboard'))
        ->assertOk()
        ->assertSee('cdn.jsdelivr.net/npm/chart.js', false)
        ->assertSee('initHalamanGuru', false);

    $this->get(route('guru.realtime'))
        ->assertOk()
        ->assertSee('loadRealtimeFromStorage', false)
        ->assertSee('startRealtimeAutoRefresh', false);
});

test('halaman guru tidak mengarahkan ke form identitas guru', function () {
    // Identitas guru dibaca dari database lewat `window.HADIRIN_GURU`. Kalau
    // guru belum punya kelas, halaman harus tetap tampil dengan kondisi
    // kosong, bukan dialihkan ke form identitas.
    $halaman = ['guru.dashboard', 'guru.kelola', 'guru.laporan', 'guru.realtime'];

    loginGuru();

    foreach ($halaman as $route) {
        $this->get(route($route))
            ->assertOk()
            ->assertSee('IDENTITAS_KOSONG', false)
            ->assertDontSee('/identitas-guru', false);
    }
});

test('layout sidebar menyediakan stack styles dan scripts', function () {
    loginGuru();

    $this->get(route('guru.kelola'))
        ->assertOk()
        ->assertSee('.stat-card', false)
        ->assertSee('tambahSiswa', false)
        ->assertSee('hapusSiswa', false);
});

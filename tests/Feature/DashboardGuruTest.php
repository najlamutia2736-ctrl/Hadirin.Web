<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('lima halaman dashboard guru dapat diakses dan memakai layout sidebar', function () {
    $halaman = [
        'guru.dashboard' => 'Dashboard',
        'guru.progres' => 'Progres Absensi',
        'guru.kelola' => 'Kelola Data Kelas',
        'guru.laporan' => 'Laporan Bulanan',
        'guru.realtime' => 'Real-Time Monitoring',
    ];

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
    $this->get(route('guru.progres'))
        ->assertOk()
        ->assertSee(route('guru.dashboard'), false)
        ->assertSee(route('guru.realtime'), false)
        ->assertSee('Kembali ke Beranda')
        // Menu CMS tidak boleh bocor ke halaman guru
        ->assertDontSee(route('cms.users'), false);
});

test('sidebar cms tetap memakai menu cms dan tidak menampilkan menu guru', function () {
    $this->get(route('cms.dashboard'))
        ->assertOk()
        ->assertSee(route('cms.users'), false)
        ->assertSee(route('cms.rekap'), false)
        ->assertDontSee(route('guru.progres'), false);
});

test('halaman guru memuat data layer dan script yang dibutuhkan', function () {
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
    // Identitas guru hanya dibaca dari localStorage di sisi browser. Kalau
    // belum diisi, halaman harus tetap tampil memakai nilai bawaan.
    $halaman = ['guru.dashboard', 'guru.progres', 'guru.kelola', 'guru.laporan', 'guru.realtime'];

    foreach ($halaman as $route) {
        $this->get(route($route))
            ->assertOk()
            ->assertSee('DEFAULT_IDENTITAS', false)
            ->assertDontSee('/identitas-guru', false);
    }
});

test('layout sidebar menyediakan stack styles dan scripts', function () {
    $this->get(route('guru.kelola'))
        ->assertOk()
        ->assertSee('.stat-card', false)
        ->assertSee('tambahSiswa', false)
        ->assertSee('hapusSiswa', false);
});

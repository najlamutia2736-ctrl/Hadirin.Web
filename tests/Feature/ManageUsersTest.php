<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('halaman manajemen pengguna menampilkan data dari database', function () {
    User::factory()->create(['name' => 'Ahmad Fauzi']);

    $this->get(route('cms.users'))
        ->assertOk()
        ->assertSee('Manajemen Pengguna')
        ->assertSee('Ahmad Fauzi');
});

test('pengguna baru dapat ditambahkan melalui modal tambah', function () {
    $response = $this->post(route('cms.users.store'), [
        'name' => 'Dewi Lestari',
        'email' => 'dewi.lestari@sekolah.sch.id',
        'role' => 'Guru',
        'password' => 'rahasia123',
    ]);

    $response
        ->assertRedirect(route('cms.users'))
        ->assertSessionHas('success', 'Pengguna berhasil ditambahkan.');

    $this->assertDatabaseHas('users', [
        'email' => 'dewi.lestari@sekolah.sch.id',
        'role' => 'Guru',
        'name' => 'Dewi Lestari',
    ]);
});

test('form tambah pengguna menolak email yang sudah terdaftar', function () {
    User::factory()->create(['email' => 'ahmad.fauzi@sekolah.sch.id']);

    $response = $this->from(route('cms.users'))->post(route('cms.users.store'), [
        'name' => 'Ahmad Fauzi Lain',
        'email' => 'ahmad.fauzi@sekolah.sch.id',
        'role' => 'Admin',
        'password' => 'rahasia123',
    ]);

    $response
        ->assertRedirect(route('cms.users'))
        ->assertSessionHasErrors('email');

    $this->assertDatabaseCount('users', 1);
});

test('form tambah pengguna memvalidasi input wajib dan panjang password', function () {
    $this->from(route('cms.users'))
        ->post(route('cms.users.store'), [
            'name' => '',
            'email' => 'bukan-email',
            'role' => 'Super Admin',
            'password' => 'pendek',
        ])
        ->assertRedirect(route('cms.users'))
        ->assertSessionHasErrors(['name', 'email', 'role', 'password']);
});

test('pengguna yang baru ditambahkan muncul di tabel setelah redirect', function () {
    $this->post(route('cms.users.store'), [
        'name' => 'Rina Wijaya',
        'email' => 'rina.wijaya@sekolah.sch.id',
        'role' => 'Siswa',
        'password' => 'rahasia123',
    ]);

    $this->get(route('cms.users'))
        ->assertOk()
        ->assertSee('Rina Wijaya')
        ->assertSee('rina.wijaya@sekolah.sch.id')
        ->assertSee('Pengguna berhasil ditambahkan.');
});

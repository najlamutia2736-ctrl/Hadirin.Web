<?php

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('halaman manajemen siswa menampilkan data dari database', function () {
    $user = User::factory()->create(['name' => 'Rina Wijaya']);
    Siswa::factory()->create([
        'user_id' => $user->id,
        'nisn' => '20240101',
        'kelas' => 'X-A',
    ]);

    $this->get(route('cms.students'))
        ->assertOk()
        ->assertSee('Manajemen Siswa')
        ->assertSee('Rina Wijaya')
        ->assertSee('20240101');
});

test('siswa baru dapat ditambahkan melalui modal tambah', function () {
    $response = $this->post(route('cms.students.store'), [
        'name' => 'Dewi Lestari',
        'nis' => '20240102',
        'class' => 'X-B',
        'gender' => 'P',
        'parent' => 'Ibu Ratna',
        'phone' => '081234567890',
    ]);

    $response
        ->assertRedirect(route('cms.students'))
        ->assertSessionHas('success', 'Siswa berhasil ditambahkan.');

    $this->assertDatabaseHas('users', [
        'name' => 'Dewi Lestari',
        'email' => '20240102@siswa.sekolah.sch.id',
        'role' => 'Siswa',
        'status' => 'Aktif',
    ]);

    $user = User::where('email', '20240102@siswa.sekolah.sch.id')->firstOrFail();

    $this->assertDatabaseHas('siswas', [
        'user_id' => $user->id,
        'nisn' => '20240102',
        'kelas' => 'X-B',
        'jenis_kelamin' => 'P',
        'wali' => 'Ibu Ratna',
        'telepon_wali' => '081234567890',
        'status' => 'Aktif',
    ]);
});

test('form tambah siswa menolak NIS yang sudah terdaftar', function () {
    $siswa = Siswa::factory()->create(['nisn' => '20240101']);

    $response = $this->from(route('cms.students'))->post(route('cms.students.store'), [
        'name' => 'Calon Siswa',
        'nis' => '20240101',
        'class' => 'X-A',
        'gender' => 'L',
    ]);

    $response
        ->assertRedirect(route('cms.students'))
        ->assertSessionHasErrors('nis');

    $this->assertDatabaseCount('siswas', 1);
    $this->assertDatabaseCount('users', 1);
    expect($siswa->nisn)->toBe('20240101');
});

test('form tambah siswa memvalidasi input wajib dan pilihan yang diizinkan', function () {
    $this->from(route('cms.students'))
        ->post(route('cms.students.store'), [
            'name' => '',
            'nis' => '123',
            'class' => 'X-Z',
            'gender' => 'X',
        ])
        ->assertRedirect(route('cms.students'))
        ->assertSessionHasErrors(['name', 'nis', 'class', 'gender']);

    $this->assertDatabaseCount('siswas', 0);
    $this->assertDatabaseCount('users', 0);
});

test('siswa yang baru ditambahkan muncul di tabel setelah redirect', function () {
    $this->post(route('cms.students.store'), [
        'name' => 'Bagus Saputra',
        'nis' => '20230222',
        'class' => 'XI-B',
        'gender' => 'L',
    ]);

    $this->get(route('cms.students'))
        ->assertOk()
        ->assertSee('Bagus Saputra')
        ->assertSee('20230222')
        ->assertSee('Siswa berhasil ditambahkan.');
});

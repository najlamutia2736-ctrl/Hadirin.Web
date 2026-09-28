<?php

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('halaman manajemen siswa menampilkan data dan aksi dari database', function () {
    $user = User::factory()->create(['name' => 'Rina Wijaya']);
    $student = Siswa::factory()->create([
        'user_id' => $user->id,
        'nisn' => '20240101',
        'kelas' => 'X-A',
    ]);

    $this->get(route('cms.student'))
        ->assertOk()
        ->assertSee('Manajemen Siswa')
        ->assertSee('Rina Wijaya')
        ->assertSee('20240101')
        ->assertSee('data-student-detail', false)
        ->assertSee('modal-detail', false)
        ->assertSee(route('cms.student.edit', $student), false)
        ->assertSee(route('cms.student.destroy', $student), false);
});

test('siswa baru dapat ditambahkan melalui modal tambah', function () {
    $response = $this->post(route('cms.student.store'), [
        'name' => 'Dewi Lestari',
        'nis' => '20240102',
        'class' => 'X-B',
        'gender' => 'P',
        'parent' => 'Ibu Ratna',
        'phone' => '081234567890',
    ]);

    $response
        ->assertRedirect(route('cms.student'))
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

    $response = $this->from(route('cms.student'))->post(route('cms.student.store'), [
        'name' => 'Calon Siswa',
        'nis' => '20240101',
        'class' => 'X-A',
        'gender' => 'L',
    ]);

    $response
        ->assertRedirect(route('cms.student'))
        ->assertSessionHasErrors('nis');

    $this->assertDatabaseCount('siswas', 1);
    $this->assertDatabaseCount('users', 1);
    expect($siswa->nisn)->toBe('20240101');
});

test('form tambah siswa memvalidasi input wajib dan pilihan yang diizinkan', function () {
    $this->from(route('cms.student'))
        ->post(route('cms.student.store'), [
            'name' => '',
            'nis' => '123',
            'class' => 'X-Z',
            'gender' => 'X',
        ])
        ->assertRedirect(route('cms.student'))
        ->assertSessionHasErrors(['name', 'nis', 'class', 'gender']);

    $this->assertDatabaseCount('siswas', 0);
    $this->assertDatabaseCount('users', 0);
});

test('siswa yang baru ditambahkan muncul di tabel setelah redirect', function () {
    $this->post(route('cms.student.store'), [
        'name' => 'Bagus Saputra',
        'nis' => '20230222',
        'class' => 'XI-B',
        'gender' => 'L',
    ]);

    $this->get(route('cms.student'))
        ->assertOk()
        ->assertSee('Bagus Saputra')
        ->assertSee('20230222')
        ->assertSee('Siswa berhasil ditambahkan.');
});

test('halaman tambah siswa menampilkan form', function () {
    $this->get(route('cms.student.create'))
        ->assertOk()
        ->assertSee('Tambah Siswa')
        ->assertSee('Nomor Telepon Wali')
        ->assertSee(route('cms.student.store'), false);
});

test('halaman edit siswa menampilkan data siswa', function () {
    $user = User::factory()->create(['name' => 'Rina Wijaya']);
    $student = Siswa::factory()->create([
        'user_id' => $user->id,
        'nisn' => '20240101',
        'kelas' => 'X-A',
    ]);

    $this->get(route('cms.student.edit', $student))
        ->assertOk()
        ->assertSee('Edit Siswa')
        ->assertSee($user->name)
        ->assertSee($student->nisn)
        ->assertSee(route('cms.student.update', $student), false);
});

test('siswa dapat diperbarui', function () {
    $user = User::factory()->create();
    $student = Siswa::factory()->create([
        'user_id' => $user->id,
        'nisn' => '20240101',
        'kelas' => 'X-A',
        'jenis_kelamin' => 'P',
        'status' => 'Aktif',
    ]);

    $response = $this->from(route('cms.student.edit', $student))->put(route('cms.student.update', $student), [
        'name' => 'Rina Aulia',
        'nis' => '20240999',
        'class' => 'XI-B',
        'gender' => 'P',
        'parent' => 'Ibu Ratna',
        'phone' => '081298765432',
        'status' => 'Pindah',
    ]);

    $response
        ->assertRedirect(route('cms.student'))
        ->assertSessionHas('success', 'Siswa berhasil diperbarui.');

    $student->refresh();
    $user->refresh();

    expect($student->nisn)->toBe('20240999')
        ->and($student->kelas)->toBe('XI-B')
        ->and($student->jenis_kelamin)->toBe('P')
        ->and($student->wali)->toBe('Ibu Ratna')
        ->and($student->telepon_wali)->toBe('081298765432')
        ->and($student->status)->toBe('Pindah')
        ->and($user->name)->toBe('Rina Aulia')
        ->and($user->email)->toBe('20240999@siswa.sekolah.sch.id')
        ->and($user->status)->toBe('Nonaktif');
});

test('form edit siswa menolak NIS milik siswa lain', function () {
    $student = Siswa::factory()->create(['nisn' => '20240101']);
    $otherStudent = Siswa::factory()->create(['nisn' => '20240102']);

    $this->from(route('cms.student.edit', $student))
        ->put(route('cms.student.update', $student), [
            'name' => 'Rina Wijaya',
            'nis' => $otherStudent->nisn,
            'class' => 'X-A',
            'gender' => 'P',
            'status' => 'Aktif',
        ])
        ->assertRedirect(route('cms.student.edit', $student))
        ->assertSessionHasErrors('nis');

    expect($student->refresh()->nisn)->toBe('20240101');
});

test('siswa dan akunnya dapat dihapus', function () {
    $user = User::factory()->create();
    $student = Siswa::factory()->create(['user_id' => $user->id]);

    $this->delete(route('cms.student.destroy', $student))
        ->assertRedirect(route('cms.student'))
        ->assertSessionHas('success', 'Siswa berhasil dihapus.');

    $this->assertModelMissing($student);
    $this->assertModelMissing($user);
});

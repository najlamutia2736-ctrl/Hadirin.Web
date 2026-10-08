<?php

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
| Manajemen kelas berada di area CMS, jadi setiap test di file ini dijalankan
| sambil login sebagai admin.
*/
beforeEach(function () {
    loginAdmin();
});

test('halaman kelas menampilkan data dari database', function () {
    $user = User::factory()->create(['name' => 'Budi Santoso']);
    $teacher = Guru::create([
        'user_id' => $user->id,
        'nip' => '198203122011012004',
        'mata_pelajaran' => 'Matematika',
    ]);
    $class = Kelas::factory()->create([
        'nama_kelas' => 'X-A',
        'tingkat' => 'X',
        'wali_kelas_id' => $teacher->id,
        'ruang' => 'R. 101',
    ]);

    $this->get(route('cms.classes'))
        ->assertOk()
        ->assertSee('Manajemen Kelas')
        ->assertSee($class->nama_kelas)
        ->assertSee('Budi Santoso')
        ->assertSee('R. 101')
        ->assertSee(route('cms.classes.store'), false)
        ->assertSee(route('cms.classes.edit', $class), false)
        ->assertSee(route('cms.classes.destroy', $class), false);
});

test('halaman tambah kelas menampilkan form', function () {
    $this->get(route('cms.classes.create'))
        ->assertOk()
        ->assertSee('Tambah Kelas')
        ->assertSee('Wali Kelas')
        ->assertSee(route('cms.classes.store'), false);
});

test('kelas baru dapat ditambahkan melalui form tambah kelas', function () {
    $user = User::factory()->create();
    $teacher = Guru::create([
        'user_id' => $user->id,
        'nip' => '198203122011012005',
        'mata_pelajaran' => 'Bahasa Indonesia',
    ]);

    $response = $this->from(route('cms.classes'))->post(route('cms.classes.store'), [
        'name' => 'X-D',
        'level' => 'X',
        'homeroom' => $teacher->id,
        'room' => 'R. 104',
        'tahun_ajaran' => 2026,
    ]);

    $response
        ->assertRedirect(route('cms.classes'))
        ->assertSessionHas('success', 'Kelas berhasil ditambahkan.');

    $this->assertDatabaseHas('kelas', [
        'nama_kelas' => 'X-D',
        'tingkat' => 'X',
        'wali_kelas_id' => $teacher->id,
        'ruang' => 'R. 104',
        'tahun_ajaran' => 2026,
        'status' => 'Aktif',
    ]);
});

test('halaman edit kelas menampilkan data kelas', function () {
    $class = Kelas::factory()->create([
        'nama_kelas' => 'X-A',
        'tingkat' => 'X',
        'ruang' => 'R. 101',
    ]);

    $this->get(route('cms.classes.edit', $class))
        ->assertOk()
        ->assertSee('Edit Kelas')
        ->assertSee($class->nama_kelas)
        ->assertSee(route('cms.classes.update', $class), false);
});

test('kelas dapat diperbarui', function () {
    $class = Kelas::factory()->create([
        'nama_kelas' => 'X-A',
        'tingkat' => 'X',
        'ruang' => 'R. 101',
    ]);

    $response = $this->from(route('cms.classes.edit', $class))->put(route('cms.classes.update', $class), [
        'name' => 'X-D',
        'level' => 'XI',
        'room' => 'R. 204',
        'tahun_ajaran' => 2026,
        'status' => 'Arsip',
    ]);

    $response
        ->assertRedirect(route('cms.classes'))
        ->assertSessionHas('success', 'Kelas berhasil diperbarui.');

    $this->assertDatabaseHas('kelas', [
        'id' => $class->id,
        'nama_kelas' => 'X-D',
        'tingkat' => 'XI',
        'ruang' => 'R. 204',
        'tahun_ajaran' => 2026,
        'status' => 'Arsip',
    ]);
});

test('form kelas offers option belum ada pengampu', function () {
    $this->get(route('cms.classes.create'))
        ->assertOk()
        ->assertSee('Belum ada pengampu');
});

test('opsi belum ada pengampu tidak dianggap guru pengampu', function () {
    $class = Kelas::factory()->create();
    $subject = MataPelajaran::create([
        'kode_mata_pelajaran' => 'MP-1',
        'nama_mata_pelajaran' => 'Matematika',
    ]);
    $teacher = Guru::create([
        'user_id' => User::factory()->create()->id,
        'nip' => '198203122011012006',
        'mata_pelajaran_id' => $subject->id,
    ]);
    $class->guru()->attach($teacher->id);

    $this->from(route('cms.classes.edit', $class))
        ->put(route('cms.classes.update', $class), [
            'name' => $class->nama_kelas,
            'level' => $class->tingkat,
            'tahun_ajaran' => $class->tahun_ajaran,
            'teachers' => [''],
        ])
        ->assertRedirect(route('cms.classes'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseMissing('guru_kelas', [
        'kelas_id' => $class->id,
        'guru_id' => $teacher->id,
    ]);
});

test('kelas dapat dihapus', function () {
    $class = Kelas::factory()->create();

    $this->delete(route('cms.classes.destroy', $class))
        ->assertRedirect(route('cms.classes'))
        ->assertSessionHas('success', 'Kelas berhasil dihapus.');

    $this->assertDatabaseMissing('kelas', [
        'id' => $class->id,
    ]);
});

test('nama kelas harus unik', function () {
    Kelas::factory()->create(['nama_kelas' => 'X-A']);

    $this->from(route('cms.classes'))
        ->post(route('cms.classes.store'), [
            'name' => 'X-A',
            'level' => 'X',
            'tahun_ajaran' => 2026,
        ])
        ->assertSessionHasErrors('name');
});

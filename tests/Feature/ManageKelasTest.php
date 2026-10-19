<?php

use App\Models\Dosen;
use App\Models\Kelas;
use App\Models\MataKuliah;
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
    $dosen = Dosen::create([
        'user_id' => $user->id,
        'nidn' => '198203122011012004',
        'mata_kuliah' => 'Matematika',
    ]);
    $class = Kelas::factory()->create([
        'nama_kelas' => 'Kelas A',
        'tingkat' => 'Semester 1',
        'pembimbing_akademik_id' => $dosen->id,
        'ruang' => 'R. 101',
    ]);

    $this->get(route('cms.kelas'))
        ->assertOk()
        ->assertSee('Manajemen Kelas')
        ->assertSee($class->nama_kelas)
        ->assertSee('Budi Santoso')
        ->assertSee('R. 101')
        ->assertSee(route('cms.kelas.store'), false)
        ->assertSee(route('cms.kelas.edit', $class), false)
        ->assertSee(route('cms.kelas.destroy', $class), false);
});

test('halaman tambah kelas menampilkan form', function () {
    $this->get(route('cms.kelas.create'))
        ->assertOk()
        ->assertSee('Tambah Kelas')
        ->assertSee('Pembimbing Akademik')
        ->assertSee(route('cms.kelas.store'), false);
});

test('kelas baru dapat ditambahkan melalui form tambah kelas', function () {
    $user = User::factory()->create();
    $dosen = Dosen::create([
        'user_id' => $user->id,
        'nidn' => '198203122011012005',
        'mata_kuliah' => 'Bahasa Indonesia',
    ]);

    $response = $this->from(route('cms.kelas'))->post(route('cms.kelas.store'), [
        'name' => 'Kelas D',
        'level' => 'Semester 1',
        'homeroom' => $dosen->id,
        'room' => 'R. 104',
        'tahun_akademik' => 2026,
    ]);

    $response
        ->assertRedirect(route('cms.kelas'))
        ->assertSessionHas('success', 'Kelas berhasil ditambahkan.');

    $this->assertDatabaseHas('kelas', [
        'nama_kelas' => 'Kelas D',
        'tingkat' => 'Semester 1',
        'pembimbing_akademik_id' => $dosen->id,
        'ruang' => 'R. 104',
        'tahun_akademik' => 2026,
        'status' => 'Aktif',
    ]);
});

test('halaman edit kelas menampilkan data kelas', function () {
    $class = Kelas::factory()->create([
        'nama_kelas' => 'Kelas A',
        'tingkat' => 'Semester 1',
        'ruang' => 'R. 101',
    ]);

    $this->get(route('cms.kelas.edit', $class))
        ->assertOk()
        ->assertSee('Edit Kelas')
        ->assertSee($class->nama_kelas)
        ->assertSee(route('cms.kelas.update', $class), false);
});

test('kelas dapat diperbarui', function () {
    $class = Kelas::factory()->create([
        'nama_kelas' => 'Kelas A',
        'tingkat' => 'Semester 1',
        'ruang' => 'R. 101',
    ]);

    $response = $this->from(route('cms.kelas.edit', $class))->put(route('cms.kelas.update', $class), [
        'name' => 'Kelas D',
        'level' => 'Semester 2',
        'room' => 'R. 204',
        'tahun_akademik' => 2026,
        'status' => 'Arsip',
    ]);

    $response
        ->assertRedirect(route('cms.kelas'))
        ->assertSessionHas('success', 'Kelas berhasil diperbarui.');

    $this->assertDatabaseHas('kelas', [
        'id' => $class->id,
        'nama_kelas' => 'Kelas D',
        'tingkat' => 'Semester 2',
        'ruang' => 'R. 204',
        'tahun_akademik' => 2026,
        'status' => 'Arsip',
    ]);
});

test('form kelas offers option belum ada pengampu', function () {
    $this->get(route('cms.kelas.create'))
        ->assertOk()
        ->assertSee('Belum ada pengampu');
});

test('opsi belum ada pengampu tidak dianggap dosen pengampu', function () {
    $class = Kelas::factory()->create();
    $subject = MataKuliah::create([
        'kode_mata_kuliah' => 'MP-1',
        'nama_mata_kuliah' => 'Matematika',
    ]);
    $dosen = Dosen::create([
        'user_id' => User::factory()->create()->id,
        'nidn' => '198203122011012006',
        'mata_kuliah_id' => $subject->id,
    ]);
    $class->dosen()->attach($dosen->id);

    $this->from(route('cms.kelas.edit', $class))
        ->put(route('cms.kelas.update', $class), [
            'name' => $class->nama_kelas,
            'level' => $class->tingkat,
            'tahun_akademik' => $class->tahun_akademik,
            'dosens' => [''],
        ])
        ->assertRedirect(route('cms.kelas'))
        ->assertSessionHasNoErrors();

    $this->assertDatabaseMissing('dosen_kelas', [
        'kelas_id' => $class->id,
        'dosen_id' => $dosen->id,
    ]);
});

test('kelas dapat dihapus', function () {
    $class = Kelas::factory()->create();

    $this->delete(route('cms.kelas.destroy', $class))
        ->assertRedirect(route('cms.kelas'))
        ->assertSessionHas('success', 'Kelas berhasil dihapus.');

    $this->assertDatabaseMissing('kelas', [
        'id' => $class->id,
    ]);
});

test('nama kelas harus unik', function () {
    Kelas::factory()->create(['nama_kelas' => 'Kelas A']);

    $this->from(route('cms.kelas'))
        ->post(route('cms.kelas.store'), [
            'name' => 'Kelas A',
        'level' => 'Semester 1',
            'tahun_akademik' => 2026,
        ])
        ->assertSessionHasErrors('name');
});

<?php

use App\Models\Guru;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
| Manajemen guru berada di area CMS, jadi setiap test di file ini dijalankan
| sambil login sebagai admin.
*/
beforeEach(function () {
    loginAdmin();
});

test('halaman guru menampilkan data dari database', function () {
    $user = User::factory()->create(['name' => 'Siti Nurhaliza, S.Pd.']);
    $teacher = Guru::create([
        'user_id' => $user->id,
        'nip' => '198703122011012004',
        'mata_pelajaran' => 'Matematika',
        'telepon' => '081234567890',
        'status' => 'Aktif',
    ]);

    $this->get(route('cms.teachers'))
        ->assertOk()
        ->assertSee('Manajemen Guru')
        ->assertSee($teacher->nip)
        ->assertSee($user->name)
        ->assertSee('Matematika')
        ->assertSee(route('cms.teachers.store'), false)
        ->assertSee(route('cms.teachers.edit', $teacher), false)
        ->assertSee(route('cms.teachers.destroy', $teacher), false);
});

test('halaman tambah guru menampilkan form', function () {
    $this->get(route('cms.teachers.create'))
        ->assertOk()
        ->assertSee('Tambah Guru')
        ->assertSee('Mata Pelajaran')
        ->assertSee(route('cms.teachers.store'), false);
});

test('guru baru dapat ditambahkan melalui form tambah guru', function () {
    $response = $this->from(route('cms.teachers'))->post(route('cms.teachers.store'), [
        'name' => 'Budi Santoso, M.Pd.',
        'nip' => '198205102008012006',
        'subject' => 'Bahasa Indonesia',
        'phone' => '081398765432',
        'status' => 'Aktif',
    ]);

    $response
        ->assertRedirect(route('cms.teachers'))
        ->assertSessionHas('success', 'Guru berhasil ditambahkan.');

    $user = User::where('email', '198205102008012006@guru.sekolah.sch.id')->firstOrFail();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Budi Santoso, M.Pd.',
        'role' => 'Guru',
        'status' => 'Aktif',
    ]);

    $this->assertDatabaseHas('gurus', [
        'user_id' => $user->id,
        'nip' => '198205102008012006',
        'mata_pelajaran' => 'Bahasa Indonesia',
        'telepon' => '081398765432',
        'status' => 'Aktif',
    ]);
});

test('halaman edit guru menampilkan data guru', function () {
    $user = User::factory()->create(['name' => 'Siti Nurhaliza, S.Pd.']);
    $teacher = Guru::create([
        'user_id' => $user->id,
        'nip' => '198703122011012004',
        'mata_pelajaran' => 'Matematika',
        'telepon' => '081234567890',
        'status' => 'Aktif',
    ]);

    $this->get(route('cms.teachers.edit', $teacher))
        ->assertOk()
        ->assertSee('Edit Guru')
        ->assertSee($user->name)
        ->assertSee($teacher->nip)
        ->assertSee(route('cms.teachers.update', $teacher), false);
});

test('guru dapat diperbarui', function () {
    $user = User::factory()->create();
    $teacher = Guru::create([
        'user_id' => $user->id,
        'nip' => '198703122011012004',
        'mata_pelajaran' => 'Matematika',
        'telepon' => '081234567890',
        'status' => 'Aktif',
    ]);

    $response = $this->from(route('cms.teachers.edit', $teacher))->put(route('cms.teachers.update', $teacher), [
        'name' => 'Siti Nurhaliza, S.Pd. (Perbarui)',
        'nip' => '198703122011012099',
        'subject' => 'Bahasa Indonesia',
        'phone' => '081234567891',
        'status' => 'Cuti',
    ]);

    $response
        ->assertRedirect(route('cms.teachers'))
        ->assertSessionHas('success', 'Guru berhasil diperbarui.');

    $this->assertDatabaseHas('gurus', [
        'id' => $teacher->id,
        'nip' => '198703122011012099',
        'mata_pelajaran' => 'Bahasa Indonesia',
        'telepon' => '081234567891',
        'status' => 'Cuti',
    ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Siti Nurhaliza, S.Pd. (Perbarui)',
        'email' => '198703122011012099@guru.sekolah.sch.id',
        'status' => 'Aktif',
    ]);
});

test('guru dapat dihapus', function () {
    $user = User::factory()->create();
    $teacher = Guru::create([
        'user_id' => $user->id,
        'nip' => '198703122011012004',
        'mata_pelajaran' => 'Matematika',
        'status' => 'Aktif',
    ]);

    $this->delete(route('cms.teachers.destroy', $teacher))
        ->assertRedirect(route('cms.teachers'))
        ->assertSessionHas('success', 'Guru berhasil dihapus.');

    $this->assertDatabaseMissing('gurus', ['id' => $teacher->id]);
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('nip guru harus unik', function () {
    Guru::factory()->create(['nip' => '198205102008012006']);

    $this->from(route('cms.teachers'))
        ->post(route('cms.teachers.store'), [
            'name' => 'Guru Baru',
            'nip' => '198205102008012006',
            'subject' => 'Informatika',
            'status' => 'Aktif',
        ])
        ->assertSessionHasErrors('nip');
});

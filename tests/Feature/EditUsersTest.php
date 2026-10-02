<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('halaman edit menampilkan data pengguna', function () {
    $user = User::factory()->create([
        'name' => 'Ahmad Fauzi',
        'email' => 'ahmad.fauzi@sekolah.sch.id',
        'role' => 'Guru',
        'status' => 'Aktif',
    ]);

    $this->get(route('cms.users.edit', $user))
        ->assertOk()
        ->assertSee('Edit Pengguna')
        ->assertSee($user->name)
        ->assertSee($user->email)
        ->assertSee('Simpan Perubahan');

    $this->get(route('cms.users'))
        ->assertOk()
        ->assertSee(route('cms.users.edit', $user), false)
        ->assertSee(route('cms.users.destroy', $user), false);
});

test('pengguna dapat diperbarui tanpa mengubah password', function () {
    $user = User::factory()->create([
        'name' => 'Ahmad Fauzi',
        'email' => 'ahmad.fauzi@sekolah.sch.id',
        'role' => 'Guru',
        'status' => 'Aktif',
    ]);

    $response = $this->from(route('cms.users.edit', $user))->put(route('cms.users.update', $user), [
        'name' => 'Ahmad Fauzi_updated',
        'email' => 'ahmad.fauzi.updated@sekolah.sch.id',
        'role' => 'Admin',
        'status' => 'Nonaktif',
        'password' => '',
    ]);

    $response
        ->assertRedirect(route('cms.users'))
        ->assertSessionHas('success', 'Pengguna berhasil diperbarui.');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Ahmad Fauzi_updated',
        'email' => 'ahmad.fauzi.updated@sekolah.sch.id',
        'role' => 'Admin',
        'status' => 'Nonaktif',
    ]);
});

test('pengguna dapat dihapus', function () {
    $user = User::factory()->create([
        'name' => 'Pengguna Akan Dihapus',
    ]);

    $this->delete(route('cms.users.destroy', $user))
        ->assertRedirect(route('cms.users'))
        ->assertSessionHas('success', 'Pengguna berhasil dihapus.');

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);
});

test('email pengguna tidak dapat digunakan pengguna lain', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create([
        'email' => 'email.terpakai@sekolah.sch.id',
    ]);

    $this->from(route('cms.users.edit', $user))
        ->put(route('cms.users.update', $user), [
            'name' => $user->name,
            'email' => $otherUser->email,
            'role' => $user->role,
            'status' => $user->status,
        ])
        ->assertSessionHasErrors('email');
});

test('pengguna boleh menyimpan emailnya sendiri tanpa perubahan', function () {
    // `unique` harus dikecualikan untuk baris yang sedang diedit, kalau tidak
    // pengguna tidak akan bisa menyimpan tanpa mengubah email.
    $user = User::factory()->create([
        'email' => 'ahmad.fauzi@sekolah.sch.id',
        'status' => 'Aktif',
    ]);

    $this->from(route('cms.users.edit', $user))
        ->put(route('cms.users.update', $user), [
            'name' => 'Ahmad Fauzi',
            'email' => 'ahmad.fauzi@sekolah.sch.id',
            'role' => 'Guru',
            'status' => 'Nonaktif',
        ])
        ->assertSessionHas('success', 'Pengguna berhasil diperbarui.');

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email' => 'ahmad.fauzi@sekolah.sch.id',
        'status' => 'Nonaktif',
    ]);
});

test('password baru pada form ubah harus dikonfirmasi', function () {
    $user = User::factory()->create([
        'password' => 'passwordlama123',
        'status' => 'Aktif',
    ]);

    $this->from(route('cms.users.edit', $user))
        ->put(route('cms.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
            'password' => 'passwordbaru123',
            'password_confirmation' => 'passwordlain123',
        ])
        ->assertSessionHasErrors('password');

    // Password lama harus tetap utuh karena simpanan gagal.
    $this->assertTrue(
        Hash::check('passwordlama123', $user->fresh()->password)
    );
});

test('password baru yang terkonfirmasi benar-benar mengganti password lama', function () {
    $user = User::factory()->create([
        'password' => 'passwordlama123',
        'status' => 'Aktif',
    ]);

    $this->from(route('cms.users.edit', $user))
        ->put(route('cms.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
            'password' => 'passwordbaru123',
            'password_confirmation' => 'passwordbaru123',
        ])
        ->assertSessionHas('success');

    $this->assertTrue(
        Hash::check('passwordbaru123', $user->fresh()->password)
    );
});

test('status wajib diisi saat ubah juga', function () {
    $user = User::factory()->create(['status' => 'Aktif']);

    $this->from(route('cms.users.edit', $user))
        ->put(route('cms.users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ])
        ->assertSessionHasErrors('status');
});

<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

<?php

use App\Models\Kelas;
use App\Models\MataPelajaran;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('halaman mata pelajaran menampilkan data dari database', function () {
    MataPelajaran::create([
        'kode_mata_pelajaran' => 'UJI1',
        'nama_mata_pelajaran' => 'Matematika Uji',
    ]);

    $this->get(route('cms.mata-pelajaran'))
        ->assertOk()
        ->assertSee('Manajemen Mata Pelajaran')
        ->assertSee('Matematika Uji')
        ->assertSee('UJI1')
        ->assertSee(route('cms.mata-pelajaran.create'), false)
        ->assertSee(route('cms.mata-pelajaran.store'), false);
});

test('halaman mata pelajaran menampilkan jumlah kelas per mata pelajaran', function () {
    $mataPelajaran = MataPelajaran::create([
        'kode_mata_pelajaran' => 'UJI5',
        'nama_mata_pelajaran' => 'Informatika Uji',
    ]);

    Kelas::factory()->count(2)->create(['mata_pelajaran_id' => $mataPelajaran->id]);

    $this->get(route('cms.mata-pelajaran'))
        ->assertOk()
        ->assertSee('Informatika Uji')
        ->assertSee('2', false)
        ->assertSee('kelas');
});

test('halaman mata pelajaran bisa dicari', function () {
    MataPelajaran::create(['kode_mata_pelajaran' => 'UJI1', 'nama_mata_pelajaran' => 'Matematika Uji']);
    MataPelajaran::create(['kode_mata_pelajaran' => 'UJI2', 'nama_mata_pelajaran' => 'Informatika Uji']);

    $this->get(route('cms.mata-pelajaran', ['q' => 'Informatika']))
        ->assertOk()
        ->assertSee('Informatika Uji')
        ->assertDontSee('Matematika Uji');
});

test('halaman tambah mata pelajaran menampilkan form', function () {
    $this->get(route('cms.mata-pelajaran.create'))
        ->assertOk()
        ->assertSee('Tambah Mata Pelajaran')
        ->assertSee('Kode Mata Pelajaran')
        ->assertSee('Nama Mata Pelajaran')
        ->assertSee(route('cms.mata-pelajaran.store'), false);
});

test('mata pelajaran baru dapat ditambahkan', function () {
    $response = $this->from(route('cms.mata-pelajaran'))->post(route('cms.mata-pelajaran.store'), [
        'code' => 'UJI1',
        'name' => 'Matematika Uji',
    ]);

    $response
        ->assertRedirect(route('cms.mata-pelajaran'))
        ->assertSessionHas('success', 'Mata pelajaran berhasil ditambahkan.');

    $this->assertDatabaseHas('mata_pelajaran', [
        'kode_mata_pelajaran' => 'UJI1',
        'nama_mata_pelajaran' => 'Matematika Uji',
    ]);
});

/*
| Kode sering diketik dengan huruf kecil atau spasi, misalnya "mtk" atau
| "M T K". Semua itu harus berakhir jadi satu kode yang sama, bukan tiga data.
*/

test('kode mata pelajaran dinormalisasi ke huruf kapital tanpa spasi', function () {
    $this->post(route('cms.mata-pelajaran.store'), [
        'code' => '  u j i  ',
        'name' => 'Matematika Uji',
    ]);

    $this->assertDatabaseHas('mata_pelajaran', [
        'kode_mata_pelajaran' => 'UJI',
    ]);
});

test('halaman edit mata pelajaran menampilkan data mata pelajaran', function () {
    $mataPelajaran = MataPelajaran::create([
        'kode_mata_pelajaran' => 'UJI2',
        'nama_mata_pelajaran' => 'Informatika Uji',
    ]);

    $this->get(route('cms.mata-pelajaran.edit', $mataPelajaran))
        ->assertOk()
        ->assertSee('Edit Mata Pelajaran')
        ->assertSee('UJI2')
        ->assertSee('Informatika Uji')
        ->assertSee(route('cms.mata-pelajaran.update', $mataPelajaran), false);
});

test('mata pelajaran dapat diperbarui', function () {
    $mataPelajaran = MataPelajaran::create([
        'kode_mata_pelajaran' => 'UJI2',
        'nama_mata_pelajaran' => 'Informatika Uji',
    ]);

    $this->from(route('cms.mata-pelajaran.edit', $mataPelajaran))
        ->put(route('cms.mata-pelajaran.update', $mataPelajaran), [
            'code' => 'UJI3',
            'name' => 'Informatika Diperbarui',
        ])
        ->assertRedirect(route('cms.mata-pelajaran'))
        ->assertSessionHas('success', 'Mata pelajaran berhasil diperbarui.');

    $this->assertDatabaseHas('mata_pelajaran', [
        'id' => $mataPelajaran->id,
        'kode_mata_pelajaran' => 'UJI3',
        'nama_mata_pelajaran' => 'Informatika Diperbarui',
    ]);
});

test('mata pelajaran dapat dihapus', function () {
    $mataPelajaran = MataPelajaran::create([
        'kode_mata_pelajaran' => 'UJI2',
        'nama_mata_pelajaran' => 'Informatika Uji',
    ]);

    $this->delete(route('cms.mata-pelajaran.destroy', $mataPelajaran))
        ->assertRedirect(route('cms.mata-pelajaran'))
        ->assertSessionHas('success', 'Mata pelajaran berhasil dihapus.');

    $this->assertDatabaseMissing('mata_pelajaran', ['id' => $mataPelajaran->id]);
});

/*
| `kelas.mata_pelajaran_id` memakai `nullOnDelete`, jadi menghapus mata pelajaran
| tidak boleh ikut menghapus kelas yang memakainya.
*/

test('menghapus mata pelajaran tidak menghapus kelas yang memakainya', function () {
    $mataPelajaran = MataPelajaran::create([
        'kode_mata_pelajaran' => 'UJI2',
        'nama_mata_pelajaran' => 'Informatika Uji',
    ]);

    $kelas = Kelas::factory()->create(['mata_pelajaran_id' => $mataPelajaran->id]);

    $this->delete(route('cms.mata-pelajaran.destroy', $mataPelajaran));

    $this->assertDatabaseMissing('mata_pelajaran', ['id' => $mataPelajaran->id]);

    $this->assertDatabaseHas('kelas', [
        'id' => $kelas->id,
        'mata_pelajaran_id' => null,
    ]);
});

test('kode mata pelajaran harus unik', function () {
    MataPelajaran::create(['kode_mata_pelajaran' => 'UJI1', 'nama_mata_pelajaran' => 'Matematika Uji']);

    $this->from(route('cms.mata-pelajaran'))
        ->post(route('cms.mata-pelajaran.store'), [
            'code' => 'UJI1',
            'name' => 'Nama Mata Pelajaran Lain',
        ])
        ->assertSessionHasErrors('code')
        ->assertSessionHasErrors([
            'code' => 'Kode mata pelajaran sudah dipakai.',
        ]);

    $this->assertDatabaseMissing('mata_pelajaran', ['nama_mata_pelajaran' => 'Nama Mata Pelajaran Lain']);
});

test('nama mata pelajaran harus unik', function () {
    MataPelajaran::create(['kode_mata_pelajaran' => 'UJI1', 'nama_mata_pelajaran' => 'Matematika Uji']);

    $this->from(route('cms.mata-pelajaran'))
        ->post(route('cms.mata-pelajaran.store'), [
            'code' => 'UJI4',
            'name' => 'Matematika Uji',
        ])
        ->assertSessionHasErrors('name')
        ->assertSessionHasErrors([
            'name' => 'Nama mata pelajaran sudah dipakai.',
        ]);

    $this->assertDatabaseMissing('mata_pelajaran', ['kode_mata_pelajaran' => 'UJI4']);
});

test('ubah mata pelajaran boleh menyimpan kode dan nama sendiri', function () {
    $mataPelajaran = MataPelajaran::create([
        'kode_mata_pelajaran' => 'UJI1',
        'nama_mata_pelajaran' => 'Matematika Uji',
    ]);

    // Uniknya harus mengabaikan baris yang sedang diedit, kalau tidak maka
    // setiap penyimpanan pasti ditolak karena kodenya memang miliknya sendiri.
    $this->put(route('cms.mata-pelajaran.update', $mataPelajaran), [
        'code' => 'UJI1',
        'name' => 'Matematika Uji',
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseHas('mata_pelajaran', [
        'id' => $mataPelajaran->id,
        'kode_mata_pelajaran' => 'UJI1',
        'nama_mata_pelajaran' => 'Matematika Uji',
    ]);
});

test('mata pelajaran wajib punya kode dan nama', function () {
    $this->from(route('cms.mata-pelajaran'))
        ->post(route('cms.mata-pelajaran.store'), [])
        ->assertSessionHasErrors(['code', 'name']);
});

test('mata pelajaran muncul di sidebar cms', function () {
    $this->get(route('cms.mata-pelajaran'))
        ->assertOk()
        ->assertSee('Mata Pelajaran', false);
});

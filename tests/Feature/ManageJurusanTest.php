<?php

use App\Models\Jurusan;
use App\Models\Kelas;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('halaman jurusan menampilkan data dari database', function () {
    Jurusan::create([
        'kode_jurusan' => 'UJI1',
        'nama_jurusan' => 'Rekayasa Perangkat Lunak Uji',
    ]);

    $this->get(route('cms.jurusan'))
        ->assertOk()
        ->assertSee('Manajemen Jurusan')
        ->assertSee('Rekayasa Perangkat Lunak Uji')
        ->assertSee('UJI1')
        ->assertSee(route('cms.jurusan.create'), false)
        ->assertSee(route('cms.jurusan.store'), false);
});

test('halaman jurusan menampilkan jumlah kelas per jurusan', function () {
    $jurusan = Jurusan::create([
        'kode_jurusan' => 'UJI5',
        'nama_jurusan' => 'Teknik Komputer Jaringan Uji',
    ]);

    Kelas::factory()->count(2)->create(['jurusan_id' => $jurusan->id]);

    $this->get(route('cms.jurusan'))
        ->assertOk()
        ->assertSee('Teknik Komputer Jaringan Uji')
        ->assertSee('2', false)
        ->assertSee('kelas');
});

test('halaman jurusan bisa dicari', function () {
    Jurusan::create(['kode_jurusan' => 'UJI1', 'nama_jurusan' => 'Rekayasa Perangkat Lunak Uji']);
    Jurusan::create(['kode_jurusan' => 'UJI2', 'nama_jurusan' => 'Desain Komunikasi Visual Uji']);

    $this->get(route('cms.jurusan', ['q' => 'Desain']))
        ->assertOk()
        ->assertSee('Desain Komunikasi Visual Uji')
        ->assertDontSee('Rekayasa Perangkat Lunak Uji');
});

test('halaman tambah jurusan menampilkan form', function () {
    $this->get(route('cms.jurusan.create'))
        ->assertOk()
        ->assertSee('Tambah Jurusan')
        ->assertSee('Kode Jurusan')
        ->assertSee('Nama Jurusan')
        ->assertSee(route('cms.jurusan.store'), false);
});

test('jurusan baru dapat ditambahkan', function () {
    $response = $this->from(route('cms.jurusan'))->post(route('cms.jurusan.store'), [
        'code' => 'UJI1',
        'name' => 'Rekayasa Perangkat Lunak Uji',
    ]);

    $response
        ->assertRedirect(route('cms.jurusan'))
        ->assertSessionHas('success', 'Jurusan berhasil ditambahkan.');

    $this->assertDatabaseHas('jurusan', [
        'kode_jurusan' => 'UJI1',
        'nama_jurusan' => 'Rekayasa Perangkat Lunak Uji',
    ]);
});

/*
| Kode sering diketik dengan huruf kecil atau spasi, misalnya "rpl" atau
| "R P L". Semua itu harus berakhir jadi satu kode yang sama, bukan tiga data.
*/

test('kode jurusan dinormalisasi ke huruf kapital tanpa spasi', function () {
    $this->post(route('cms.jurusan.store'), [
        'code' => '  u j i  ',
        'name' => 'Rekayasa Perangkat Lunak Uji',
    ]);

    $this->assertDatabaseHas('jurusan', [
        'kode_jurusan' => 'UJI',
    ]);
});

test('halaman edit jurusan menampilkan data jurusan', function () {
    $jurusan = Jurusan::create([
        'kode_jurusan' => 'UJI2',
        'nama_jurusan' => 'Desain Komunikasi Visual Uji',
    ]);

    $this->get(route('cms.jurusan.edit', $jurusan))
        ->assertOk()
        ->assertSee('Edit Jurusan')
        ->assertSee('UJI2')
        ->assertSee('Desain Komunikasi Visual Uji')
        ->assertSee(route('cms.jurusan.update', $jurusan), false);
});

test('jurusan dapat diperbarui', function () {
    $jurusan = Jurusan::create([
        'kode_jurusan' => 'UJI2',
        'nama_jurusan' => 'Desain Komunikasi Visual Uji',
    ]);

    $this->from(route('cms.jurusan.edit', $jurusan))
        ->put(route('cms.jurusan.update', $jurusan), [
            'code' => 'UJI3',
            'name' => 'Desain Komunikasi Visual Diperbarui',
        ])
        ->assertRedirect(route('cms.jurusan'))
        ->assertSessionHas('success', 'Jurusan berhasil diperbarui.');

    $this->assertDatabaseHas('jurusan', [
        'id' => $jurusan->id,
        'kode_jurusan' => 'UJI3',
        'nama_jurusan' => 'Desain Komunikasi Visual Diperbarui',
    ]);
});

test('jurusan dapat dihapus', function () {
    $jurusan = Jurusan::create([
        'kode_jurusan' => 'UJI2',
        'nama_jurusan' => 'Desain Komunikasi Visual Uji',
    ]);

    $this->delete(route('cms.jurusan.destroy', $jurusan))
        ->assertRedirect(route('cms.jurusan'))
        ->assertSessionHas('success', 'Jurusan berhasil dihapus.');

    $this->assertDatabaseMissing('jurusan', ['id' => $jurusan->id]);
});

/*
| `kelas.jurusan_id` memakai `nullOnDelete`, jadi menghapus jurusan tidak
| boleh ikut menghapus kelas yang memakainya.
*/

test('menghapus jurusan tidak menghapus kelas yang memakainya', function () {
    $jurusan = Jurusan::create([
        'kode_jurusan' => 'UJI2',
        'nama_jurusan' => 'Desain Komunikasi Visual Uji',
    ]);

    $kelas = Kelas::factory()->create(['jurusan_id' => $jurusan->id]);

    $this->delete(route('cms.jurusan.destroy', $jurusan));

    $this->assertDatabaseMissing('jurusan', ['id' => $jurusan->id]);

    $this->assertDatabaseHas('kelas', [
        'id' => $kelas->id,
        'jurusan_id' => null,
    ]);
});

test('kode jurusan harus unik', function () {
    Jurusan::create(['kode_jurusan' => 'UJI1', 'nama_jurusan' => 'Rekayasa Perangkat Lunak Uji']);

    $this->from(route('cms.jurusan'))
        ->post(route('cms.jurusan.store'), [
            'code' => 'UJI1',
            'name' => 'Nama Jurusan Lain',
        ])
        ->assertSessionHasErrors('code')
        ->assertSessionHasErrors([
            'code' => 'Kode jurusan sudah dipakai.',
        ]);

    $this->assertDatabaseMissing('jurusan', ['nama_jurusan' => 'Nama Jurusan Lain']);
});

test('nama jurusan harus unik', function () {
    Jurusan::create(['kode_jurusan' => 'UJI1', 'nama_jurusan' => 'Rekayasa Perangkat Lunak Uji']);

    $this->from(route('cms.jurusan'))
        ->post(route('cms.jurusan.store'), [
            'code' => 'UJI4',
            'name' => 'Rekayasa Perangkat Lunak Uji',
        ])
        ->assertSessionHasErrors('name')
        ->assertSessionHasErrors([
            'name' => 'Nama jurusan sudah dipakai.',
        ]);

    $this->assertDatabaseMissing('jurusan', ['kode_jurusan' => 'UJI4']);
});

test('ubah jurusan boleh menyimpan kode dan nama sendiri', function () {
    $jurusan = Jurusan::create([
        'kode_jurusan' => 'UJI1',
        'nama_jurusan' => 'Rekayasa Perangkat Lunak Uji',
    ]);

    // Uniknya harus mengabaikan baris yang sedang diedit, kalau tidak maka
    // setiap penyimpanan pasti ditolak karena kodenya memang miliknya sendiri.
    $this->put(route('cms.jurusan.update', $jurusan), [
        'code' => 'UJI1',
        'name' => 'Rekayasa Perangkat Lunak Uji',
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseHas('jurusan', [
        'id' => $jurusan->id,
        'kode_jurusan' => 'UJI1',
        'nama_jurusan' => 'Rekayasa Perangkat Lunak Uji',
    ]);
});

test('jurusan wajib punya kode dan nama', function () {
    $this->from(route('cms.jurusan'))
        ->post(route('cms.jurusan.store'), [])
        ->assertSessionHasErrors(['code', 'name']);
});

test('jurusan muncul di sidebar cms', function () {
    $this->get(route('cms.jurusan'))
        ->assertOk()
        ->assertSee('Departments', false);
});

<?php

use App\Models\Dosen;
use App\Models\Kelas;
use App\Models\MataKuliah;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
| Manajemen mata kuliah berada di area CMS, jadi setiap test di file ini
| dijalankan sambil login sebagai admin.
*/
beforeEach(function () {
    loginAdmin();
});

test('halaman mata kuliah menampilkan data dari database', function () {
    MataKuliah::create([
        'kode_mata_kuliah' => 'UJI1',
        'nama_mata_kuliah' => 'Matematika Uji',
    ]);

    $this->get(route('cms.mata-kuliah'))
        ->assertOk()
        ->assertSee('Manajemen Mata Kuliah')
        ->assertSee('Matematika Uji')
        ->assertSee('UJI1')
        ->assertSee(route('cms.mata-kuliah.create'), false)
        ->assertSee(route('cms.mata-kuliah.store'), false);
});

test('halaman mata kuliah menampilkan jumlah kelas per mata kuliah', function () {
    $mataKuliah = MataKuliah::create([
        'kode_mata_kuliah' => 'UJI5',
        'nama_mata_kuliah' => 'Informatika Uji',
    ]);

    Kelas::factory()->count(2)->create(['mata_kuliah_id' => $mataKuliah->id]);

    $this->get(route('cms.mata-kuliah'))
        ->assertOk()
        ->assertSee('Informatika Uji')
        ->assertSee('2', false)
        ->assertSee('kelas');
});

test('halaman mata kuliah bisa dicari', function () {
    MataKuliah::create(['kode_mata_kuliah' => 'UJI1', 'nama_mata_kuliah' => 'Matematika Uji']);
    MataKuliah::create(['kode_mata_kuliah' => 'UJI2', 'nama_mata_kuliah' => 'Informatika Uji']);

    $this->get(route('cms.mata-kuliah', ['q' => 'Informatika']))
        ->assertOk()
        ->assertSee('Informatika Uji')
        ->assertDontSee('Matematika Uji');
});

test('halaman tambah mata kuliah menampilkan form', function () {
    $this->get(route('cms.mata-kuliah.create'))
        ->assertOk()
        ->assertSee('Tambah Mata Kuliah')
        ->assertSee('Kode Mata Kuliah')
        ->assertSee('Nama Mata Kuliah')
        ->assertSee(route('cms.mata-kuliah.store'), false);
});

test('mata kuliah baru dapat ditambahkan', function () {
    $response = $this->from(route('cms.mata-kuliah'))->post(route('cms.mata-kuliah.store'), [
        'code' => 'UJI1',
        'name' => 'Matematika Uji',
    ]);

    $response
        ->assertRedirect(route('cms.mata-kuliah'))
        ->assertSessionHas('success', 'Mata pelajaran berhasil ditambahkan.');

    $this->assertDatabaseHas('mata_kuliah', [
        'kode_mata_kuliah' => 'UJI1',
        'nama_mata_kuliah' => 'Matematika Uji',
    ]);
});

/*
| Kode sering diketik dengan huruf kecil atau spasi, misalnya "mtk" atau
| "M T K". Semua itu harus berakhir jadi satu kode yang sama, bukan tiga data.
*/

test('kode mata kuliah dinormalisasi ke huruf kapital tanpa spasi', function () {
    $this->post(route('cms.mata-kuliah.store'), [
        'code' => '  u j i  ',
        'name' => 'Matematika Uji',
    ]);

    $this->assertDatabaseHas('mata_kuliah', [
        'kode_mata_kuliah' => 'UJI',
    ]);
});

test('halaman edit mata kuliah menampilkan data mata kuliah', function () {
    $mataKuliah = MataKuliah::create([
        'kode_mata_kuliah' => 'UJI2',
        'nama_mata_kuliah' => 'Informatika Uji',
    ]);

    $this->get(route('cms.mata-kuliah.edit', $mataKuliah))
        ->assertOk()
        ->assertSee('Edit Mata Kuliah')
        ->assertSee('UJI2')
        ->assertSee('Informatika Uji')
        ->assertSee(route('cms.mata-kuliah.update', $mataKuliah), false);
});

test('mata kuliah dapat diperbarui', function () {
    $mataKuliah = MataKuliah::create([
        'kode_mata_kuliah' => 'UJI2',
        'nama_mata_kuliah' => 'Informatika Uji',
    ]);

    $this->from(route('cms.mata-kuliah.edit', $mataKuliah))
        ->put(route('cms.mata-kuliah.update', $mataKuliah), [
            'code' => 'UJI3',
            'name' => 'Informatika Diperbarui',
        ])
        ->assertRedirect(route('cms.mata-kuliah'))
        ->assertSessionHas('success', 'Mata pelajaran berhasil diperbarui.');

    $this->assertDatabaseHas('mata_kuliah', [
        'id' => $mataKuliah->id,
        'kode_mata_kuliah' => 'UJI3',
        'nama_mata_kuliah' => 'Informatika Diperbarui',
    ]);
});

test('mata kuliah dapat dihapus', function () {
    $mataKuliah = MataKuliah::create([
        'kode_mata_kuliah' => 'UJI2',
        'nama_mata_kuliah' => 'Informatika Uji',
    ]);

    $this->delete(route('cms.mata-kuliah.destroy', $mataKuliah))
        ->assertRedirect(route('cms.mata-kuliah'))
        ->assertSessionHas('success', 'Mata pelajaran berhasil dihapus.');

    $this->assertDatabaseMissing('mata_kuliah', ['id' => $mataKuliah->id]);
});

/*
| `kelas.mata_kuliah_id` memakai `nullOnDelete`, jadi menghapus mata kuliah
| tidak boleh ikut menghapus kelas yang memakainya.
*/

test('menghapus mata kuliah tidak menghapus kelas yang memakainya', function () {
    $mataKuliah = MataKuliah::create([
        'kode_mata_kuliah' => 'UJI2',
        'nama_mata_kuliah' => 'Informatika Uji',
    ]);

    $kelas = Kelas::factory()->create(['mata_kuliah_id' => $mataKuliah->id]);

    $this->delete(route('cms.mata-kuliah.destroy', $mataKuliah));

    $this->assertDatabaseMissing('mata_kuliah', ['id' => $mataKuliah->id]);

    $this->assertDatabaseHas('kelas', [
        'id' => $kelas->id,
        'mata_kuliah_id' => null,
    ]);
});

test('kode mata kuliah harus unik', function () {
    MataKuliah::create(['kode_mata_kuliah' => 'UJI1', 'nama_mata_kuliah' => 'Matematika Uji']);

    $this->from(route('cms.mata-kuliah'))
        ->post(route('cms.mata-kuliah.store'), [
            'code' => 'UJI1',
            'name' => 'Nama Mata Kuliah Lain',
        ])
        ->assertSessionHasErrors('code')
        ->assertSessionHasErrors([
            'code' => 'Kode mata kuliah sudah dipakai.',
        ]);

    $this->assertDatabaseMissing('mata_kuliah', ['nama_mata_kuliah' => 'Nama Mata Kuliah Lain']);
});

test('nama mata kuliah harus unik', function () {
    MataKuliah::create(['kode_mata_kuliah' => 'UJI1', 'nama_mata_kuliah' => 'Matematika Uji']);

    $this->from(route('cms.mata-kuliah'))
        ->post(route('cms.mata-kuliah.store'), [
            'code' => 'UJI4',
            'name' => 'Matematika Uji',
        ])
        ->assertSessionHasErrors('name')
        ->assertSessionHasErrors([
            'name' => 'Nama mata kuliah sudah dipakai.',
        ]);

    $this->assertDatabaseMissing('mata_kuliah', ['kode_mata_kuliah' => 'UJI4']);
});

test('ubah mata kuliah boleh menyimpan kode dan nama sendiri', function () {
    $mataKuliah = MataKuliah::create([
        'kode_mata_kuliah' => 'UJI1',
        'nama_mata_kuliah' => 'Matematika Uji',
    ]);

    // Uniknya harus mengabaikan baris yang sedang diedit, kalau tidak maka
    // setiap penyimpanan pasti ditolak karena kodenya memang miliknya sendiri.
    $this->put(route('cms.mata-kuliah.update', $mataKuliah), [
        'code' => 'UJI1',
        'name' => 'Matematika Uji',
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseHas('mata_kuliah', [
        'id' => $mataKuliah->id,
        'kode_mata_kuliah' => 'UJI1',
        'nama_mata_kuliah' => 'Matematika Uji',
    ]);
});

test('mata kuliah wajib punya kode dan nama', function () {
    $this->from(route('cms.mata-kuliah'))
        ->post(route('cms.mata-kuliah.store'), [])
        ->assertSessionHasErrors(['code', 'name']);
});

test('mata kuliah muncul di sidebar cms', function () {
    $this->get(route('cms.mata-kuliah'))
        ->assertOk()
        ->assertSee('Mata Kuliah', false);
});

/*
| Dosen pengajar ditampilkan dan ditetapkan lewat relasi `dosens.mata_kuliah_id`,
| bukan kolom baru di tabel `mata_kuliah`: satu dosen menunjuk ke satu mata
| kuliah, dan halaman mata kuliah menampilkan nama dosen pengajar di tiap baris.
| Form dosen wajib diisi hanya kalau ada dosen yang bisa dipilih, supaya
| instalasi baru tanpa data dosen tetap bisa mengisi daftar mata kuliah.
*/

test('halaman mata kuliah menampilkan dosen pengajar tiap mata kuliah', function () {
    $mataKuliah = MataKuliah::create([
        'kode_mata_kuliah' => 'UJI1',
        'nama_mata_kuliah' => 'Matematika Uji',
    ]);

    $dosen = Dosen::factory()->create(['mata_kuliah_id' => $mataKuliah->id]);

    MataKuliah::create([
        'kode_mata_kuliah' => 'UJI2',
        'nama_mata_kuliah' => 'Informatika Uji',
    ]);

    $this->get(route('cms.mata-kuliah'))
        ->assertOk()
        ->assertSee('Dosen Pengajar')
        ->assertSee($dosen->user->name)
        ->assertSee('Belum ada dosen');
});

test('form tambah mata kuliah menampilkan daftar dosen pengajar', function () {
    $dosen = Dosen::factory()->create();

    $this->get(route('cms.mata-kuliah.create'))
        ->assertOk()
        ->assertSee('Dosen Pengajar')
        ->assertSee($dosen->user->name);
});

test('form tambah mata kuliah memberi tahu admin kalau belum ada dosen', function () {
    $this->get(route('cms.mata-kuliah.create'))
        ->assertOk()
        ->assertSee('Belum ada data dosen')
        ->assertSee(route('cms.dosens.create'), false);
});

test('dosen pengajar wajib dipilih saat ada dosen', function () {
    Dosen::factory()->create();

    $this->from(route('cms.mata-kuliah'))
        ->post(route('cms.mata-kuliah.store'), [
            'code' => 'UJI1',
            'name' => 'Matematika Uji',
        ])
        ->assertSessionHasErrors('dosen');

    $this->assertDatabaseMissing('mata_kuliah', ['kode_mata_kuliah' => 'UJI1']);
});

test('mata kuliah baru mengaitkan dosen pengajar yang dipilih', function () {
    $dosen = Dosen::factory()->create();

    $this->post(route('cms.mata-kuliah.store'), [
        'code' => 'UJI1',
        'name' => 'Matematika Uji',
        'dosen' => $dosen->id,
    ])->assertSessionHasNoErrors();

    $mataKuliah = MataKuliah::query()->where('kode_mata_kuliah', 'UJI1')->firstOrFail();

    $this->assertDatabaseHas('dosens', [
        'id' => $dosen->id,
        'mata_kuliah_id' => $mataKuliah->id,
    ]);
});

test('dosen pengajar bisa dipindahkan lewat form edit', function () {
    $mataKuliah = MataKuliah::create([
        'kode_mata_kuliah' => 'UJI1',
        'nama_mata_kuliah' => 'Matematika Uji',
    ]);

    $dosenLama = Dosen::factory()->create(['mata_kuliah_id' => $mataKuliah->id]);
    $dosenBaru = Dosen::factory()->create();

    // Form edit menampilkan dosen yang sekarang mengampu mata kuliah ini.
    $this->get(route('cms.mata-kuliah.edit', $mataKuliah))
        ->assertOk()
        ->assertSee($dosenLama->user->name);

    $this->from(route('cms.mata-kuliah.edit', $mataKuliah))
        ->put(route('cms.mata-kuliah.update', $mataKuliah), [
            'code' => 'UJI1',
            'name' => 'Matematika Uji',
            'dosen' => $dosenBaru->id,
        ])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('dosens', [
        'id' => $dosenBaru->id,
        'mata_kuliah_id' => $mataKuliah->id,
    ])->assertDatabaseHas('dosens', [
        'id' => $dosenLama->id,
        'mata_kuliah_id' => null,
    ]);
});

<?php

use App\Models\Dosen;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/*
| Manajemen dosen berada di area CMS, jadi setiap test di file ini dijalankan
| sambil login sebagai admin.

| Mata pelajaran dosen disimpan sebagai `mata_kuliah_id` yang menunjuk ke
| tabel `mata_kuliah`, bukan teks bebas. Karena itu test di sini memakai
| `MataKuliah` sungguhan supaya daftar mata kuliah yang dipakai form dan kode yang
| tampil di halaman benar-benar berasal dari tabel yang sama.
*/
uses(RefreshDatabase::class);

beforeEach(function () {
    loginAdmin();
});

test('halaman dosen menampilkan data dari database', function () {
    $mataKuliah = mataKuliahDenganKode('MTK', 'Matematika');

    $user = User::factory()->create(['name' => 'Siti Nurhaliza, S.Pd.']);
    $dosen = Dosen::create([
        'user_id' => $user->id,
        'nidn' => '198703122011012004',
        'mata_kuliah_id' => $mataKuliah->id,
        'telepon' => '081234567890',
        'status' => 'Aktif',
    ]);

    $this->get(route('cms.dosens'))
        ->assertOk()
        ->assertSee('Manajemen Dosen')
        ->assertSee($dosen->nidn)
        ->assertSee($user->name)
        ->assertSee('Matematika')
        // Kode mata kuliah ikut tampil supaya nama yang sama tidak ambigu.
        ->assertSee('MTK')
        ->assertSee(route('cms.dosens.store'), false)
        ->assertSee(route('cms.dosens.edit', $dosen), false)
        ->assertSee(route('cms.dosens.destroy', $dosen), false);
});

test('halaman tambah dosen menampilkan form', function () {
    $this->get(route('cms.dosens.create'))
        ->assertOk()
        ->assertSee('Tambah Dosen')
        ->assertSee('Mata Kuliah')
        ->assertSee(route('cms.dosens.store'), false);
});

test('form dosen memakai pilihan mata kuliah dari tabel mata kuliah', function () {
    $mataKuliah = mataKuliahDenganKode('DKV', 'Desain Komunikasi Visual');

    $this->get(route('cms.dosens.create'))
        ->assertOk()
        ->assertSee('Desain Komunikasi Visual (DKV)')
        ->assertSee('value="'.$mataKuliah->id.'"', false);
});

test('dosen baru dapat ditambahkan melalui form tambah dosen', function () {
    $mataKuliah = mataKuliahDenganKode('BIND', 'Bahasa Indonesia');

    $response = $this->from(route('cms.dosens'))->post(route('cms.dosens.store'), [
        'name' => 'Budi Santoso, M.Pd.',
        'nidn' => '198205102008012006',
        'subject' => $mataKuliah->id,
        'phone' => '081398765432',
        'status' => 'Aktif',
    ]);

    $response
        ->assertRedirect(route('cms.dosens'))
        ->assertSessionHas('success', 'Dosen berhasil ditambahkan.');

    $user = User::where('email', '198205102008012006@dosen.kampus.ac.id')->firstOrFail();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Budi Santoso, M.Pd.',
        'role' => 'Dosen',
        'status' => 'Aktif',
    ]);

    $this->assertDatabaseHas('dosens', [
        'user_id' => $user->id,
        'nidn' => '198205102008012006',
        'mata_kuliah_id' => $mataKuliah->id,
        'telepon' => '081398765432',
        'status' => 'Aktif',
    ]);

    // Nama mata kuliah ikut terisi dari baris mata kuliah yang dipilih.
    $dosen = Dosen::query()->where('nidn', '198205102008012006')->firstOrFail();

    expect($dosen->mata_kuliah)->toBe('Bahasa Indonesia')
        ->and($dosen->namaMataKuliah())->toBe('Bahasa Indonesia');
});

test('halaman edit dosen menampilkan data dosen', function () {
    $mataKuliah = MataKuliah::factory()->create(['nama_mata_kuliah' => 'Matematika']);

    $user = User::factory()->create(['name' => 'Siti Nurhaliza, S.Pd.']);
    $dosen = Dosen::create([
        'user_id' => $user->id,
        'nidn' => '198703122011012004',
        'mata_kuliah_id' => $mataKuliah->id,
        'telepon' => '081234567890',
        'status' => 'Aktif',
    ]);

    $this->get(route('cms.dosens.edit', $dosen))
        ->assertOk()
        ->assertSee('Edit Dosen')
        ->assertSee($user->name)
        ->assertSee($dosen->nidn)
        ->assertSee(route('cms.dosens.update', $dosen), false);
});

test('dosen dapat diperbarui', function () {
    $mataKuliahAwal = mataKuliahDenganKode('MTK', 'Matematika');
    $mataKuliahBaru = mataKuliahDenganKode('BIND', 'Bahasa Indonesia');

    $user = User::factory()->create();
    $dosen = Dosen::create([
        'user_id' => $user->id,
        'nidn' => '198703122011012004',
        'mata_kuliah_id' => $mataKuliahAwal->id,
        'telepon' => '081234567890',
        'status' => 'Aktif',
    ]);

    $response = $this->from(route('cms.dosens.edit', $dosen))->put(route('cms.dosens.update', $dosen), [
        'name' => 'Siti Nurhaliza, S.Pd. (Perbarui)',
        'nidn' => '198703122011012099',
        'subject' => $mataKuliahBaru->id,
        'phone' => '081234567891',
        'status' => 'Cuti',
    ]);

    $response
        ->assertRedirect(route('cms.dosens'))
        ->assertSessionHas('success', 'Dosen berhasil diperbarui.');

    $this->assertDatabaseHas('dosens', [
        'id' => $dosen->id,
        'nidn' => '198703122011012099',
        'mata_kuliah_id' => $mataKuliahBaru->id,
        'telepon' => '081234567891',
        'status' => 'Cuti',
    ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Siti Nurhaliza, S.Pd. (Perbarui)',
        'email' => '198703122011012099@dosen.kampus.ac.id',
        'status' => 'Aktif',
    ]);
});

test('dosen dapat dihapus', function () {
    $user = User::factory()->create();
    $dosen = Dosen::create([
        'user_id' => $user->id,
        'nidn' => '198703122011012004',
        'mata_kuliah_id' => mataKuliahDenganKode('MTK', 'Matematika')->id,
        'status' => 'Aktif',
    ]);

    $this->delete(route('cms.dosens.destroy', $dosen))
        ->assertRedirect(route('cms.dosens'))
        ->assertSessionHas('success', 'Dosen berhasil dihapus.');

    $this->assertDatabaseMissing('dosens', ['id' => $dosen->id]);
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('nidn dosen harus unik', function () {
    Dosen::factory()->create(['nidn' => '198205102008012006']);

    $this->from(route('cms.dosens'))
        ->post(route('cms.dosens.store'), [
            'name' => 'Dosen Baru',
            'nidn' => '198205102008012006',
            'subject' => mataKuliahDenganKode('INF', 'Informatika')->id,
            'status' => 'Aktif',
        ])
        ->assertSessionHasErrors('nidn');
});

test('mata kuliah dosen harus ada di tabel mata kuliah', function () {
    // Form dosen tidak lagi menerima teks bebas, jadi mata kuliah yang tidak dikenal
    // ditolak, bukan disimpan sebagai string yang tidak terhubung.
    $this->from(route('cms.dosens'))
        ->post(route('cms.dosens.store'), [
            'name' => 'Dosen Tanpa Mata Kuliah',
            'nidn' => '198205102008012007',
            'subject' => 'Informatika',
            'status' => 'Aktif',
        ])
        ->assertSessionHasErrors('subject');

    expect(Dosen::query()->where('nidn', '198205102008012007')->exists())->toBeFalse();
});

test('filter dosen berdasarkan mata kuliah memakai mata kuliah yang terhubung', function () {
    $dkv = mataKuliahDenganKode('DKV', 'Desain Komunikasi Visual');
    $rpl = mataKuliahDenganKode('RPL', 'Rekayasa Perangkat Lunak');

    $martha = Dosen::factory()->create(['mata_kuliah_id' => $dkv->id]);
    $siti = Dosen::factory()->create(['mata_kuliah_id' => $rpl->id]);

    $response = $this->get(route('cms.dosens', ['subject' => $dkv->id]));

    $response->assertOk()
        ->assertSee($martha->nidn)
        ->assertDontSee($siti->nidn);
});

test('kolom kelas diampu menampilkan kelas dari tabel dosen kelas', function () {
    // `Kelas Diampu` harus mengikuti relasi `kelasDiampu` (dosen_kelas), bukan
    // `kelas` yang hanya menunjuk kelas yang dipegang sebagai kepala kelas.
    $mataKuliah = MataKuliah::factory()->create();
    $dosen = Dosen::factory()->create(['mata_kuliah_id' => $mataKuliah->id]);

    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas DKV']);
    $kelas->dosen()->sync([$dosen->id]);

    $this->get(route('cms.dosens'))
        ->assertOk()
        ->assertSee('Kelas DKV');
});

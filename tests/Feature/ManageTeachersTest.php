<?php

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/*
| Manajemen guru berada di area CMS, jadi setiap test di file ini dijalankan
| sambil login sebagai admin.

| Mata pelajaran guru disimpan sebagai `mata_pelajaran_id` yang menunjuk ke
| tabel `mata_pelajaran`, bukan teks bebas. Karena itu test di sini memakai
| `MataPelajaran` sungguhan supaya daftar mapel yang dipakai form dan kode yang
| tampil di halaman benar-benar berasal dari tabel yang sama.
*/
uses(RefreshDatabase::class);

beforeEach(function () {
    loginAdmin();
});

test('halaman guru menampilkan data dari database', function () {
    $mapel = mapelDenganKode('MTK', 'Matematika');

    $user = User::factory()->create(['name' => 'Siti Nurhaliza, S.Pd.']);
    $teacher = Guru::create([
        'user_id' => $user->id,
        'nip' => '198703122011012004',
        'mata_pelajaran_id' => $mapel->id,
        'telepon' => '081234567890',
        'status' => 'Aktif',
    ]);

    $this->get(route('cms.teachers'))
        ->assertOk()
        ->assertSee('Manajemen Guru')
        ->assertSee($teacher->nip)
        ->assertSee($user->name)
        ->assertSee('Matematika')
        // Kode mapel ikut tampil supaya nama yang sama tidak ambigu.
        ->assertSee('MTK')
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

test('form guru memakai pilihan mapel dari tabel mata pelajaran', function () {
    $mapel = mapelDenganKode('DKV', 'Desain Komunikasi Visual');

    $this->get(route('cms.teachers.create'))
        ->assertOk()
        ->assertSee('Desain Komunikasi Visual (DKV)')
        ->assertSee('value="'.$mapel->id.'"', false);
});

test('guru baru dapat ditambahkan melalui form tambah guru', function () {
    $mapel = mapelDenganKode('BIND', 'Bahasa Indonesia');

    $response = $this->from(route('cms.teachers'))->post(route('cms.teachers.store'), [
        'name' => 'Budi Santoso, M.Pd.',
        'nip' => '198205102008012006',
        'subject' => $mapel->id,
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
        'mata_pelajaran_id' => $mapel->id,
        'telepon' => '081398765432',
        'status' => 'Aktif',
    ]);

    // Nama mapel ikut terisi dari baris mapel yang dipilih.
    $guru = Guru::query()->where('nip', '198205102008012006')->firstOrFail();

    expect($guru->mata_pelajaran)->toBe('Bahasa Indonesia')
        ->and($guru->namaMataPelajaran())->toBe('Bahasa Indonesia');
});

test('halaman edit guru menampilkan data guru', function () {
    $mapel = MataPelajaran::factory()->create(['nama_mata_pelajaran' => 'Matematika']);

    $user = User::factory()->create(['name' => 'Siti Nurhaliza, S.Pd.']);
    $teacher = Guru::create([
        'user_id' => $user->id,
        'nip' => '198703122011012004',
        'mata_pelajaran_id' => $mapel->id,
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
    $mapelAwal = mapelDenganKode('MTK', 'Matematika');
    $mapelBaru = mapelDenganKode('BIND', 'Bahasa Indonesia');

    $user = User::factory()->create();
    $teacher = Guru::create([
        'user_id' => $user->id,
        'nip' => '198703122011012004',
        'mata_pelajaran_id' => $mapelAwal->id,
        'telepon' => '081234567890',
        'status' => 'Aktif',
    ]);

    $response = $this->from(route('cms.teachers.edit', $teacher))->put(route('cms.teachers.update', $teacher), [
        'name' => 'Siti Nurhaliza, S.Pd. (Perbarui)',
        'nip' => '198703122011012099',
        'subject' => $mapelBaru->id,
        'phone' => '081234567891',
        'status' => 'Cuti',
    ]);

    $response
        ->assertRedirect(route('cms.teachers'))
        ->assertSessionHas('success', 'Guru berhasil diperbarui.');

    $this->assertDatabaseHas('gurus', [
        'id' => $teacher->id,
        'nip' => '198703122011012099',
        'mata_pelajaran_id' => $mapelBaru->id,
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
        'mata_pelajaran_id' => mapelDenganKode('MTK', 'Matematika')->id,
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
            'subject' => mapelDenganKode('INF', 'Informatika')->id,
            'status' => 'Aktif',
        ])
        ->assertSessionHasErrors('nip');
});

test('mata pelajaran guru harus ada di tabel mata pelajaran', function () {
    // Form guru tidak lagi menerima teks bebas, jadi mapel yang tidak dikenal
    // ditolak, bukan disimpan sebagai string yang tidak terhubung.
    $this->from(route('cms.teachers'))
        ->post(route('cms.teachers.store'), [
            'name' => 'Guru Tanpa Mapel',
            'nip' => '198205102008012007',
            'subject' => 'Informatika',
            'status' => 'Aktif',
        ])
        ->assertSessionHasErrors('subject');

    expect(Guru::query()->where('nip', '198205102008012007')->exists())->toBeFalse();
});

test('filter guru berdasarkan mata pelajaran memakai mapel yang terhubung', function () {
    $dkv = mapelDenganKode('DKV', 'Desain Komunikasi Visual');
    $rpl = mapelDenganKode('RPL', 'Rekayasa Perangkat Lunak');

    $martha = Guru::factory()->create(['mata_pelajaran_id' => $dkv->id]);
    $siti = Guru::factory()->create(['mata_pelajaran_id' => $rpl->id]);

    $response = $this->get(route('cms.teachers', ['subject' => $dkv->id]));

    $response->assertOk()
        ->assertSee($martha->nip)
        ->assertDontSee($siti->nip);
});

test('kolom kelas diampu menampilkan kelas dari tabel guru kelas', function () {
    // `Kelas Diampu` harus mengikuti relasi `kelasDiampu` (guru_kelas), bukan
    // `kelas` yang hanya menunjuk kelas yang dipegang sebagai kepala kelas.
    $mapel = MataPelajaran::factory()->create();
    $guru = Guru::factory()->create(['mata_pelajaran_id' => $mapel->id]);

    $kelas = Kelas::factory()->create(['nama_kelas' => 'XII-DKV']);
    $kelas->guru()->sync([$guru->id]);

    $this->get(route('cms.teachers'))
        ->assertOk()
        ->assertSee('XII-DKV');
});

<?php

use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
| Manajemen mahasiswa berada di area CMS, jadi setiap test di file ini dijalankan
| sambil login sebagai admin.
*/
beforeEach(function () {
    loginAdmin();
});

/*
| Form mahasiswa hanya menerima kelas yang benar-benar ada di tabel `kelas`, jadi
| setiap test yang mengirim `class` perlu menyiapkan kelasnya lebih dulu.
*/
function kelas(string $namaKelas): Kelas
{
    return Kelas::factory()->create(['nama_kelas' => $namaKelas]);
}

test('halaman manajemen mahasiswa menampilkan data dan aksi dari database', function () {
    $user = User::factory()->create(['name' => 'Rina Wijaya']);
    $mahasiswa = Mahasiswa::factory()->create([
        'user_id' => $user->id,
        'nim' => '20240101',
        'kelas' => 'Kelas A',
    ]);

    $this->get(route('cms.mahasiswa'))
        ->assertOk()
        ->assertSee('Manajemen Mahasiswa')
        ->assertSee('Rina Wijaya')
        ->assertSee('20240101')
        ->assertSee('data-mahasiswa-detail', false)
        ->assertSee('modal-detail', false)
        ->assertSee(route('cms.mahasiswa.edit', $mahasiswa), false)
        ->assertSee(route('cms.mahasiswa.destroy', $mahasiswa), false);
});

test('mahasiswa baru dapat ditambahkan melalui modal tambah', function () {
    kelas('Kelas B');

    $response = $this->post(route('cms.mahasiswa.store'), [
        'name' => 'Dewi Lestari',
        'nim' => '20240102',
        'class' => 'Kelas B',
        'gender' => 'P',
        'parent' => 'Ibu Ratna',
        'phone' => '081234567890',
    ]);

    $response
        ->assertRedirect(route('cms.mahasiswa'))
        ->assertSessionHas('success', 'Mahasiswa berhasil ditambahkan.');

    $this->assertDatabaseHas('users', [
        'name' => 'Dewi Lestari',
        'email' => '20240102@mahasiswa.kampus.ac.id',
        'role' => 'Mahasiswa',
        'status' => 'Aktif',
    ]);

    $user = User::where('email', '20240102@mahasiswa.kampus.ac.id')->firstOrFail();

    $this->assertDatabaseHas('mahasiswa', [
        'user_id' => $user->id,
        'nim' => '20240102',
        'kelas' => 'Kelas B',
        'jenis_kelamin' => 'P',
        'wali' => 'Ibu Ratna',
        'telepon_wali' => '081234567890',
        'status' => 'Aktif',
    ]);
});

test('form tambah mahasiswa menolak NIM yang sudah terdaftar', function () {
    $mahasiswa = Mahasiswa::factory()->create(['nim' => '20240101']);
    kelas('Kelas A');

    $response = $this->from(route('cms.mahasiswa'))->post(route('cms.mahasiswa.store'), [
        'name' => 'Calon Mahasiswa',
        'nim' => '20240101',
        'class' => 'Kelas A',
        'gender' => 'L',
    ]);

    $response
        ->assertRedirect(route('cms.mahasiswa'))
        ->assertSessionHasErrors('nim');

    $this->assertDatabaseCount('mahasiswa', 1);
    // Akun mahasiswa tidak boleh ikut dibuat ulang, dan akun admin dari
    // `loginAdmin()` juga tidak boleh berubah jadi akun mahasiswa.
    expect(User::query()->where('role', 'Mahasiswa')->count())->toBe(1)
        ->and($mahasiswa->nim)->toBe('20240101');
});

test('filter kelas pada halaman mahasiswa', function () {
    $a = Mahasiswa::factory()->create(['kelas' => 'Kelas A', 'status' => 'Aktif']);
    $b = Mahasiswa::factory()->create(['kelas' => 'Kelas H', 'status' => 'Aktif']);
    $c = Mahasiswa::factory()->create(['kelas' => 'Kelas H', 'status' => 'Pindah']);

    $this->get(route('cms.mahasiswa', ['kelas' => 'Kelas H']))
        ->assertOk()
        ->assertSee($b->user->name)
        ->assertSee($c->user->name)
        ->assertDontSee($a->user->name);

    $this->get(route('cms.mahasiswa', ['kelas' => 'Kelas A']))
        ->assertOk()
        ->assertSee($a->user->name)
        ->assertDontSee($b->user->name);

    $this->get(route('cms.mahasiswa', ['kelas' => '']))
        ->assertOk()
        ->assertSee($a->user->name)
        ->assertSee($b->user->name)
        ->assertSee($c->user->name);
});

test('filter status pada halaman mahasiswa', function () {
    $aktif = Mahasiswa::factory()->create(['status' => 'Aktif']);
    $pindah = Mahasiswa::factory()->create(['status' => 'Pindah']);

    $this->get(route('cms.mahasiswa', ['status' => 'Pindah']))
        ->assertOk()
        ->assertSee($pindah->user->name)
        ->assertDontSee($aktif->user->name);

    $this->get(route('cms.mahasiswa', ['status' => 'Nonaktif']))
        ->assertOk()
        ->assertSee('Tidak ada mahasiswa yang cocok dengan filter.');
});

test('filter kelas dan status bisa digabung', function () {
    $cocok = Mahasiswa::factory()->create(['kelas' => 'Kelas H', 'status' => 'Aktif']);
    $kelasBeda = Mahasiswa::factory()->create(['kelas' => 'Kelas A', 'status' => 'Aktif']);
    $statusBeda = Mahasiswa::factory()->create(['kelas' => 'Kelas H', 'status' => 'Pindah']);

    $this->get(route('cms.mahasiswa', ['kelas' => 'Kelas H', 'status' => 'Aktif']))
        ->assertOk()
        ->assertSee($cocok->user->name)
        ->assertDontSee($kelasBeda->user->name)
        ->assertDontSee($statusBeda->user->name);
});

test('dropdown kelas dan status dibangun dari data mahasiswa', function () {
    // Kelas di luar daftar baku tetap bisa difilter.
    Mahasiswa::factory()->create(['kelas' => 'Kelas X', 'status' => 'Alpa']);

    $this->get(route('cms.mahasiswa'))
        ->assertOk()
        ->assertSee('<option value="Kelas X"', false)
        ->assertSee('<option value="Alpa"', false);
});

test('filter yang tidak dikenal diabaikan, bukan menghasilkan halaman kosong', function () {
    $mahasiswa = Mahasiswa::factory()->create(['kelas' => 'Kelas A']);

    $this->get(route('cms.mahasiswa', ['kelas' => 'KELAS-HILANG', 'status' => 'STATUS-HILANG']))
        ->assertOk()
        ->assertSee($mahasiswa->user->name);
});

test('halaman mahasiswa menampilkan badge filter aktif dan tautan reset', function () {
    $mahasiswa = Mahasiswa::factory()->create(['kelas' => 'Kelas A', 'status' => 'Aktif']);

    $this->get(route('cms.mahasiswa', ['kelas' => 'Kelas A', 'status' => 'Aktif']))
        ->assertOk()
        ->assertSee('Filter aktif:')
        ->assertSee('Kelas A')
        ->assertSee('Status Aktif')
        ->assertSee(route('cms.mahasiswa'), false);
});

test('pencarian tetap bekerja saat digabung dengan filter kelas dan status', function () {
    Mahasiswa::factory()->create([
        'user_id' => User::factory()->create(['name' => 'Rina Wijaya'])->id,
        'kelas' => 'Kelas A',
        'status' => 'Aktif',
    ]);
    Mahasiswa::factory()->create([
        'user_id' => User::factory()->create(['name' => 'Budi Santoso'])->id,
        'kelas' => 'Kelas B',
        'status' => 'Aktif',
    ]);

    // Pencarian + kelas yang cocok memunculkan mahasiswanya.
    $this->get(route('cms.mahasiswa', ['q' => 'Rina', 'kelas' => 'Kelas A', 'status' => 'Aktif']))
        ->assertOk()
        ->assertSee('Rina Wijaya')
        ->assertDontSee('Budi Santoso');

    // Kelas yang tidak cocok membuat hasil pencarian kosong.
    $this->get(route('cms.mahasiswa', ['q' => 'Rina', 'kelas' => 'Kelas B', 'status' => 'Aktif']))
        ->assertOk()
        ->assertSee('Tidak ada mahasiswa yang cocok dengan filter.')
        ->assertDontSee('Rina Wijaya');

    // Hanya filter kelas tanpa pencarian tetap menampilkan semua yang cocok.
    $this->get(route('cms.mahasiswa', ['kelas' => 'Kelas B', 'status' => 'Aktif']))
        ->assertOk()
        ->assertSee('Budi Santoso')
        ->assertDontSee('Rina Wijaya');
});

test('halaman mahasiswa tetap menampilkan semua mahasiswa tanpa filter', function () {
    $first = Mahasiswa::factory()->create();
    $second = Mahasiswa::factory()->create();

    $this->get(route('cms.mahasiswa'))
        ->assertOk()
        ->assertSee($first->user->name)
        ->assertSee($second->user->name)
        ->assertDontSee('Filter aktif:');
});

test('form tambah mahasiswa memvalidasi input wajib dan pilihan yang diizinkan', function () {
    kelas('Kelas A');

    $this->from(route('cms.mahasiswa'))
        ->post(route('cms.mahasiswa.store'), [
            'name' => '',
            'nim' => '123',
            'class' => 'Kelas Z',
            'gender' => 'X',
        ])
        ->assertRedirect(route('cms.mahasiswa'))
        ->assertSessionHasErrors(['name', 'nim', 'class', 'gender']);

    $this->assertDatabaseCount('mahasiswa', 0);
    // Akun admin dari `loginAdmin()` tetap ada, jadi yang dicek tidak adanya
    // akun mahasiswa yang ikut terbentuk.
    expect(User::query()->where('role', 'Mahasiswa')->count())->toBe(0);
});

test('mahasiswa yang baru ditambahkan muncul di tabel setelah redirect', function () {
    kelas('Kelas F');

    $this->post(route('cms.mahasiswa.store'), [
        'name' => 'Bagus Saputra',
        'nim' => '20230222',
        'class' => 'Kelas F',
        'gender' => 'L',
    ]);

    $this->get(route('cms.mahasiswa'))
        ->assertOk()
        ->assertSee('Bagus Saputra')
        ->assertSee('20230222')
        ->assertSee('Mahasiswa berhasil ditambahkan.');
});

test('halaman tambah mahasiswa menampilkan form', function () {
    kelas('Kelas A');

    $this->get(route('cms.mahasiswa.create'))
        ->assertOk()
        ->assertSee('Tambah Mahasiswa')
        ->assertSee('Nomor Telepon Orang Tua')
        ->assertSee(route('cms.mahasiswa.store'), false);
});

test('pilihan kelas pada form mahasiswa diambil dari tabel kelas', function () {
    kelas('Kelas A');
    kelas('Kelas Y');

    foreach (['cms.mahasiswa.create', 'cms.mahasiswa'] as $route) {
        $this->get(route($route))
            ->assertOk()
            ->assertSee('<option value="Kelas A"', false)
            ->assertSee('<option value="Kelas Y"', false);
    }
});

test('form tambah mahasiswa menolak kelas yang belum terdaftar di halaman kelas', function () {
    $this->from(route('cms.mahasiswa'))
        ->post(route('cms.mahasiswa.store'), [
            'name' => 'Mahasiswa Kelas Palsu',
            'nim' => '20240777',
            'class' => 'Kelas Z',
            'gender' => 'L',
        ])
        ->assertRedirect(route('cms.mahasiswa'))
        ->assertSessionHasErrors('class')
        ->assertSessionHasErrors([
            'class' => 'Kelas tersebut belum terdaftar di halaman Classes.',
        ]);

    $this->assertDatabaseCount('mahasiswa', 0);
    expect(User::query()->where('role', 'Mahasiswa')->count())->toBe(0);
});

test('mahasiswa yang ditambahkan langsung terhitung di halaman kelas', function () {
    $kelas = kelas('Kelas A');

    $this->post(route('cms.mahasiswa.store'), [
        'name' => 'Nisa Pramesti',
        'nim' => '20240505',
        'class' => 'Kelas A',
        'gender' => 'P',
    ]);

    expect($kelas->refresh()->mahasiswa()->count())->toBe(1)
        ->and($kelas->mahasiswa()->first()->nim)->toBe('20240505');

    // Halaman Classes menampilkan jumlahnya lewat relasi `Kelas::mahasiswa()`,
    // jadi angka 1 di sana bukti mahasiswa tadi benar-benar terhubung.
    $this->get(route('cms.kelas'))
        ->assertOk()
        ->assertSee('Kelas A')
        ->assertSee('1', false);
});

test('halaman edit mahasiswa menampilkan data mahasiswa', function () {
    kelas('Kelas A');

    $user = User::factory()->create(['name' => 'Rina Wijaya']);
    $mahasiswa = Mahasiswa::factory()->create([
        'user_id' => $user->id,
        'nim' => '20240101',
        'kelas' => 'Kelas A',
    ]);

    $this->get(route('cms.mahasiswa.edit', $mahasiswa))
        ->assertOk()
        ->assertSee('Edit Mahasiswa')
        ->assertSee($user->name)
        ->assertSee($mahasiswa->nim)
        ->assertSee(route('cms.mahasiswa.update', $mahasiswa), false);
});

test('mahasiswa dapat diperbarui', function () {
    kelas('Kelas F');

    $user = User::factory()->create();
    $mahasiswa = Mahasiswa::factory()->create([
        'user_id' => $user->id,
        'nim' => '20240101',
        'kelas' => 'Kelas A',
        'jenis_kelamin' => 'P',
        'status' => 'Aktif',
    ]);

    $response = $this->from(route('cms.mahasiswa.edit', $mahasiswa))->put(route('cms.mahasiswa.update', $mahasiswa), [
        'name' => 'Rina Aulia',
        'nim' => '20240999',
        'class' => 'Kelas F',
        'gender' => 'P',
        'parent' => 'Ibu Ratna',
        'phone' => '081298765432',
        'status' => 'Pindah',
    ]);

    $response
        ->assertRedirect(route('cms.mahasiswa'))
        ->assertSessionHas('success', 'Mahasiswa berhasil diperbarui.');

    $mahasiswa->refresh();
    $user->refresh();

    expect($mahasiswa->nim)->toBe('20240999')
        ->and($mahasiswa->kelas)->toBe('Kelas F')
        ->and($mahasiswa->jenis_kelamin)->toBe('P')
        ->and($mahasiswa->wali)->toBe('Ibu Ratna')
        ->and($mahasiswa->telepon_wali)->toBe('081298765432')
        ->and($mahasiswa->status)->toBe('Pindah')
        ->and($user->name)->toBe('Rina Aulia')
        ->and($user->email)->toBe('20240999@mahasiswa.kampus.ac.id')
        ->and($user->status)->toBe('Nonaktif');
});

test('form edit mahasiswa menolak NIM milik mahasiswa lain', function () {
    kelas('Kelas A');

    $mahasiswa = Mahasiswa::factory()->create(['nim' => '20240101']);
    $mahasiswaLain = Mahasiswa::factory()->create(['nim' => '20240102']);

    $this->from(route('cms.mahasiswa.edit', $mahasiswa))
        ->put(route('cms.mahasiswa.update', $mahasiswa), [
            'name' => 'Rina Wijaya',
            'nim' => $mahasiswaLain->nim,
            'class' => 'Kelas A',
            'gender' => 'P',
            'status' => 'Aktif',
        ])
        ->assertRedirect(route('cms.mahasiswa.edit', $mahasiswa))
        ->assertSessionHasErrors('nim');

    expect($mahasiswa->refresh()->nim)->toBe('20240101');
});

test('mahasiswa dan akunnya dapat dihapus', function () {
    $user = User::factory()->create();
    $mahasiswa = Mahasiswa::factory()->create(['user_id' => $user->id]);

    $this->delete(route('cms.mahasiswa.destroy', $mahasiswa))
        ->assertRedirect(route('cms.mahasiswa'))
        ->assertSessionHas('success', 'Mahasiswa berhasil dihapus.');

    $this->assertModelMissing($mahasiswa);
    $this->assertModelMissing($user);
});

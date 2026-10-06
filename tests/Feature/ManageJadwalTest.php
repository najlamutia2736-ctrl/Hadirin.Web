<?php

use App\Http\Controllers\JadwalController;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

/*
| Jadwal berada di area CMS, jadi setiap test di file ini dijalankan sambil
| login sebagai admin.
*/
beforeEach(function () {
    loginAdmin();
});

/**
 * Guru lengkap dengan akun penggunanya, sama seperti dipakai modul lain.
 */
function guruUntukJadwal(string $nama = 'Guru Jadwal'): Guru
{
    $user = User::factory()->create(['name' => $nama]);

    return Guru::factory()->create(['user_id' => $user->id]);
}

/**
 * Kelas aktif untuk dipakai pada pengujian jadwal.
 */
function kelasUntukJadwal(string $nama = 'X-A'): Kelas
{
    return Kelas::factory()->create(['nama_kelas' => $nama, 'status' => 'Aktif']);
}

/**
 * Payload form jadwal yang valid, bisa ditimpa per pengujian.
 *
 * @param  array<string, mixed>  $ubah
 * @return array<string, mixed>
 */
function dataJadwal(array $ubah = []): array
{
    return array_merge([
        'kelas_id' => kelasUntukJadwal('X-A')->id,
        'guru_id' => guruUntukJadwal('Guru Jadwal')->id,
        'mata_pelajaran' => 'Informatika',
        'hari' => 'Senin',
        'jam_mulai' => '07:30',
        'jam_selesai' => '09:00',
        'ruang' => 'R. 101',
        'tahun_ajaran' => 2026,
        'status' => 'Aktif',
    ], $ubah);
}

/*
| Halaman jadwal di dashboard admin.
*/

test('halaman jadwal menampilkan data dari database', function () {
    $jadwal = Jadwal::factory()->create([
        'mata_pelajaran' => 'Informatika',
        'hari' => 'Rabu',
        'jam_mulai' => '10:15',
        'jam_selesai' => '11:45',
        'ruang' => 'R. 202',
    ]);

    $this->get(route('cms.jadwal'))
        ->assertOk()
        ->assertSee('Jadwal Mengajar')
        ->assertSee('Informatika')
        ->assertSee('Rabu')
        ->assertSee('R. 202')
        ->assertSee($jadwal->kelas->nama_kelas)
        ->assertSee(route('cms.jadwal.create'), false)
        ->assertSee(route('cms.jadwal.edit', $jadwal), false)
        ->assertSee(route('cms.jadwal.destroy', $jadwal), false);
});

test('rentang jam pada jadwal ditampilkan tanpa detik', function () {
    Jadwal::factory()->create([
        'jam_mulai' => '07:30',
        'jam_selesai' => '09:00',
    ]);

    $this->get(route('cms.jadwal'))
        ->assertOk()
        ->assertSee('07:30 - 09:00');
});

test('route jadwal ditangani controller, bukan closure yang mengosongkan data', function () {
    // Route closure untuk URI yang sama akan MENIMPA route controller, karena
    // Laravel menimpa entri dengan method+URI yang sama. Kalau pernah terjadi,
    // viewnya tetap tampil tapi tanpa `$kelasList` dan semuanya jadi error 500.
    $aksi = Route::getRoutes()->getByName('cms.jadwal');

    expect($aksi)->not->toBeNull()
        ->and($aksi->getActionMethod())->toBe('index')
        ->and($aksi->getControllerClass())->toBe(JadwalController::class);

    // Halaman harus benar-benar dirender, bukan mengembalikan 500.
    $this->get(route('cms.jadwal'))->assertOk();
});

test('nama route jadwal tidak terdaftar dua kali', function () {
    // Duplikasi route tidak selalu langsung terlihat, tapi membuat
    // `route('cms.jadwal')` dan request sebenarnya mengarah ke route berbeda.
    $terdaftar = [];

    foreach (Route::getRoutes() as $route) {
        $terdaftar[$route->getName()][] = $route->uri();
    }

    $ganda = array_filter($terdaftar, fn ($rows) => count($rows) > 1);

    expect($ganda)->toBe([]);
});

test('halaman tambah jadwal menampilkan form dengan pilihan kelas dan guru', function () {
    $kelas = kelasUntukJadwal('XI-B');
    $guru = guruUntukJadwal('Siti Rahayu');

    $this->get(route('cms.jadwal.create'))
        ->assertOk()
        ->assertSee('Tambah Jadwal')
        ->assertSee($kelas->nama_kelas)
        ->assertSee('Siti Rahayu')
        ->assertSee('Senin')
        ->assertSee(route('cms.jadwal.store'), false);
});

test('halaman edit jadwal menampilkan data yang sedang diedit', function () {
    $jadwal = Jadwal::factory()->create([
        'mata_pelajaran' => 'Fisika',
        'hari' => 'Jumat',
        'jam_mulai' => '08:00',
        'jam_selesai' => '09:30',
        'ruang' => 'R. 303',
        'tahun_ajaran' => 2027,
        'status' => 'Nonaktif',
    ]);

    $this->get(route('cms.jadwal.edit', $jadwal))
        ->assertOk()
        ->assertSee('Edit Jadwal')
        ->assertSee('Fisika')
        ->assertSee('Jumat')
        ->assertSee('R. 303')
        ->assertSee(route('cms.jadwal.update', $jadwal), false);
});

/*
| Simpan, ubah, dan hapus jadwal.
*/

test('jadwal baru dapat ditambahkan melalui form', function () {
    $kelas = kelasUntukJadwal('X-C');
    $guru = guruUntukJadwal('Rina Wijaya');

    $response = $this->from(route('cms.jadwal.create'))->post(route('cms.jadwal.store'), [
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'mata_pelajaran' => 'Matematika',
        'hari' => 'Selasa',
        'jam_mulai' => '07:00',
        'jam_selesai' => '08:30',
        'ruang' => 'R. 105',
        'tahun_ajaran' => 2026,
        'status' => 'Aktif',
    ]);

    $response
        ->assertRedirect(route('cms.jadwal'))
        ->assertSessionHas('success', 'Jadwal berhasil ditambahkan.');

    $this->assertDatabaseHas('jadwals', [
        'kelas_id' => $kelas->id,
        'guru_id' => $guru->id,
        'mata_pelajaran' => 'Matematika',
        'hari' => 'Selasa',
        'jam_mulai' => '07:00',
        'jam_selesai' => '08:30',
        'ruang' => 'R. 105',
        'tahun_ajaran' => 2026,
        'status' => 'Aktif',
    ]);
});

test('jadwal dapat diperbarui', function () {
    $jadwal = Jadwal::factory()->create([
        'mata_pelajaran' => 'Informatika',
        'jam_mulai' => '07:00',
        'jam_selesai' => '08:30',
    ]);

    $this->from(route('cms.jadwal.edit', $jadwal))->put(route('cms.jadwal.update', $jadwal), [
        'kelas_id' => $jadwal->kelas_id,
        'guru_id' => $jadwal->guru_id,
        'mata_pelajaran' => 'Sejarah',
        'hari' => 'Kamis',
        'jam_mulai' => '10:15',
        'jam_selesai' => '11:45',
        'ruang' => 'R. 404',
        'tahun_ajaran' => 2026,
        'status' => 'Nonaktif',
    ])
        ->assertRedirect(route('cms.jadwal'))
        ->assertSessionHas('success', 'Jadwal berhasil diperbarui.');

    $this->assertDatabaseHas('jadwals', [
        'id' => $jadwal->id,
        'mata_pelajaran' => 'Sejarah',
        'hari' => 'Kamis',
        'jam_selesai' => '11:45',
        'status' => 'Nonaktif',
    ]);
});

test('jadwal dapat dihapus', function () {
    $jadwal = Jadwal::factory()->create();

    $this->delete(route('cms.jadwal.destroy', $jadwal))
        ->assertRedirect(route('cms.jadwal'))
        ->assertSessionHas('success', 'Jadwal berhasil dihapus.');

    $this->assertDatabaseMissing('jadwals', ['id' => $jadwal->id]);
});

test('hapus kelas ikut menghapus jadwalnya', function () {
    $kelas = kelasUntukJadwal('X-D');
    $jadwal = Jadwal::factory()->create(['kelas_id' => $kelas->id]);

    $kelas->delete();

    $this->assertDatabaseMissing('jadwals', ['id' => $jadwal->id]);
});

/*
| Validasi form jadwal.
*/

test('form jadwal memvalidasi kolom wajib dan nilainya', function () {
    $this->from(route('cms.jadwal.create'))
        ->post(route('cms.jadwal.store'), [
            'kelas_id' => '',
            'guru_id' => '',
            'mata_pelajaran' => '',
            'hari' => 'Minggu',
            'jam_mulai' => 'bukan jam',
            'jam_selesai' => 'bukan jam',
            'tahun_ajaran' => 'tahun',
            'status' => 'Dibekukan',
        ])
        ->assertSessionHasErrors([
            'kelas_id',
            'guru_id',
            'mata_pelajaran',
            'hari',
            'jam_mulai',
            'jam_selesai',
            'tahun_ajaran',
            'status',
        ]);

    $this->assertDatabaseCount('jadwals', 0);
});

test('form jadwal menolak kelas dan guru yang tidak ada', function () {
    $this->from(route('cms.jadwal.create'))
        ->post(route('cms.jadwal.store'), dataJadwal([
            'kelas_id' => 999999,
            'guru_id' => 999999,
        ]))
        ->assertSessionHasErrors(['kelas_id', 'guru_id']);

    $this->assertDatabaseCount('jadwals', 0);
});

test('jam selesai harus setelah jam mulai', function () {
    $this->from(route('cms.jadwal.create'))
        ->post(route('cms.jadwal.store'), dataJadwal([
            'jam_mulai' => '09:00',
            'jam_selesai' => '07:30',
        ]))
        ->assertSessionHasErrors('jam_selesai');

    // Durasi nol juga ditolak, jam pelajaran selalu punya panjang.
    $this->from(route('cms.jadwal.create'))
        ->post(route('cms.jadwal.store'), dataJadwal([
            'jam_mulai' => '09:00',
            'jam_selesai' => '09:00',
        ]))
        ->assertSessionHasErrors('jam_selesai');

    $this->assertDatabaseCount('jadwals', 0);
});

/*
| Aturan domain jadwal: satu kelas dan satu guru tidak boleh berada di dua
| tempat pada jam yang sama.
*/

test('kelas tidak boleh punya dua jadwal yang jamnya bertumpuk', function () {
    $kelas = kelasUntukJadwal('X-A');

    Jadwal::factory()->create([
        'kelas_id' => $kelas->id,
        'hari' => 'Senin',
        'jam_mulai' => '07:30',
        'jam_selesai' => '09:00',
        'tahun_ajaran' => 2026,
        'status' => 'Aktif',
    ]);

    $this->from(route('cms.jadwal.create'))->post(route('cms.jadwal.store'), [
        'kelas_id' => $kelas->id,
        'guru_id' => guruUntukJadwal('Guru Kedua')->id,
        'mata_pelajaran' => 'Matematika',
        'hari' => 'Senin',
        'jam_mulai' => '08:00',
        'jam_selesai' => '09:30',
        'tahun_ajaran' => 2026,
        'status' => 'Aktif',
    ])->assertSessionHasErrors('jam_mulai');

    $this->assertDatabaseCount('jadwals', 1);
});

test('slot jam yang bersambung boleh berurutan di kelas yang sama', function () {
    $kelas = kelasUntukJadwal('X-A');

    Jadwal::factory()->create([
        'kelas_id' => $kelas->id,
        'hari' => 'Senin',
        'jam_mulai' => '07:30',
        'jam_selesai' => '08:30',
        'tahun_ajaran' => 2026,
        'status' => 'Aktif',
    ]);

    $this->from(route('cms.jadwal.create'))->post(route('cms.jadwal.store'), [
        'kelas_id' => $kelas->id,
        'guru_id' => guruUntukJadwal('Guru Kedua')->id,
        'mata_pelajaran' => 'Matematika',
        'hari' => 'Senin',
        'jam_mulai' => '08:30',
        'jam_selesai' => '10:00',
        'tahun_ajaran' => 2026,
        'status' => 'Aktif',
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseCount('jadwals', 2);
});

test('guru tidak boleh mengajar dua kelas pada jam yang sama', function () {
    $guru = guruUntukJadwal('Guru Sibuk');

    Jadwal::factory()->create([
        'guru_id' => $guru->id,
        'kelas_id' => kelasUntukJadwal('X-A')->id,
        'hari' => 'Rabu',
        'jam_mulai' => '07:00',
        'jam_selesai' => '08:30',
        'tahun_ajaran' => 2026,
        'status' => 'Aktif',
    ]);

    $this->from(route('cms.jadwal.create'))->post(route('cms.jadwal.store'), [
        'kelas_id' => kelasUntukJadwal('X-B')->id,
        'guru_id' => $guru->id,
        'mata_pelajaran' => 'Fisika',
        'hari' => 'Rabu',
        'jam_mulai' => '08:00',
        'jam_selesai' => '09:30',
        'tahun_ajaran' => 2026,
        'status' => 'Aktif',
    ])->assertSessionHasErrors('jam_mulai');

    $this->assertDatabaseCount('jadwals', 1);
});

test('jadwal pada hari atau tahun ajaran lain tidak dianggap bentrok', function () {
    $kelas = kelasUntukJadwal('X-A');

    Jadwal::factory()->create([
        'kelas_id' => $kelas->id,
        'guru_id' => guruUntukJadwal('Guru Awal')->id,
        'hari' => 'Senin',
        'jam_mulai' => '07:00',
        'jam_selesai' => '08:30',
        'tahun_ajaran' => 2026,
        'status' => 'Aktif',
    ]);

    // Hari berbeda.
    $this->from(route('cms.jadwal.create'))->post(route('cms.jadwal.store'), [
        'kelas_id' => $kelas->id,
        'guru_id' => guruUntukJadwal('Guru Baru')->id,
        'mata_pelajaran' => 'Matematika',
        'hari' => 'Selasa',
        'jam_mulai' => '07:00',
        'jam_selesai' => '08:30',
        'tahun_ajaran' => 2026,
        'status' => 'Aktif',
    ])->assertSessionHasNoErrors();

    // Tahun ajaran berbeda.
    $this->from(route('cms.jadwal.create'))->post(route('cms.jadwal.store'), [
        'kelas_id' => $kelas->id,
        'guru_id' => guruUntukJadwal('Guru Lain')->id,
        'mata_pelajaran' => 'Fisika',
        'hari' => 'Senin',
        'jam_mulai' => '07:00',
        'jam_selesai' => '08:30',
        'tahun_ajaran' => 2027,
        'status' => 'Aktif',
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseCount('jadwals', 3);
});

test('jadwal nonaktif tidak memblokir jadwal baru di slot yang sama', function () {
    $kelas = kelasUntukJadwal('X-A');

    Jadwal::factory()->create([
        'kelas_id' => $kelas->id,
        'hari' => 'Senin',
        'jam_mulai' => '07:00',
        'jam_selesai' => '08:30',
        'tahun_ajaran' => 2026,
        'status' => 'Nonaktif',
    ]);

    $this->from(route('cms.jadwal.create'))->post(route('cms.jadwal.store'), [
        'kelas_id' => $kelas->id,
        'guru_id' => guruUntukJadwal('Guru Pengganti')->id,
        'mata_pelajaran' => 'Informatika',
        'hari' => 'Senin',
        'jam_mulai' => '07:00',
        'jam_selesai' => '08:30',
        'tahun_ajaran' => 2026,
        'status' => 'Aktif',
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseCount('jadwals', 2);
});

test('jadwal tidak dianggap bentrok dengan dirinya sendiri saat diubah', function () {
    $jadwal = Jadwal::factory()->create([
        'kelas_id' => kelasUntukJadwal('X-A')->id,
        'guru_id' => guruUntukJadwal('Guru Tunggal')->id,
        'hari' => 'Senin',
        'jam_mulai' => '07:00',
        'jam_selesai' => '08:30',
        'tahun_ajaran' => 2026,
        'status' => 'Aktif',
    ]);

    // Hanya mengubah mata pelajaran, jamnya tetap sama.
    $this->from(route('cms.jadwal.edit', $jadwal))->put(route('cms.jadwal.update', $jadwal), [
        'kelas_id' => $jadwal->kelas_id,
        'guru_id' => $jadwal->guru_id,
        'mata_pelajaran' => 'Informatika Baru',
        'hari' => 'Senin',
        'jam_mulai' => '07:00',
        'jam_selesai' => '08:30',
        'tahun_ajaran' => 2026,
        'status' => 'Aktif',
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseHas('jadwals', [
        'id' => $jadwal->id,
        'mata_pelajaran' => 'Informatika Baru',
    ]);
});

/*
| Filter dan pencarian pada halaman jadwal.
*/

test('pencarian jadwal bisa digabung dengan filter kelas, hari, dan status', function () {
    $kelasA = kelasUntukJadwal('X-A');
    $kelasB = kelasUntukJadwal('X-B');

    $target = Jadwal::factory()->create([
        'kelas_id' => $kelasA->id,
        'mata_pelajaran' => 'Informatika',
        'hari' => 'Senin',
        'status' => 'Aktif',
    ]);

    Jadwal::factory()->create([
        'kelas_id' => $kelasB->id,
        'mata_pelajaran' => 'Fisika',
        'hari' => 'Selasa',
        'status' => 'Nonaktif',
    ]);

    $this->get(route('cms.jadwal', [
        'q' => 'Informatika',
        'kelas' => $kelasA->id,
        'hari' => 'Senin',
        'status' => 'Aktif',
    ]))
        ->assertOk()
        ->assertSee($target->mata_pelajaran)
        ->assertSee('X-A')
        ->assertDontSee('Fisika');

    // Pencarian juga menyentuh nama kelas dan nama guru.
    $this->get(route('cms.jadwal', ['q' => 'X-B']))
        ->assertOk()
        ->assertSee('Fisika')
        ->assertDontSee('Informatika');
});

test('filter jadwal yang tidak dikenal diabaikan, bukan menghasilkan halaman kosong', function () {
    Jadwal::factory()->create([
        'mata_pelajaran' => 'Informatika',
        'hari' => 'Senin',
        'status' => 'Aktif',
    ]);

    $this->get(route('cms.jadwal', ['hari' => 'Minggu', 'status' => 'Dibekukan', 'kelas' => 999999]))
        ->assertOk()
        ->assertSee('Informatika');
});

test('pagination jadwal mempertahankan filter yang aktif', function () {
    $kelas = kelasUntukJadwal('X-A');

    Jadwal::factory()->count(12)->create([
        'kelas_id' => $kelas->id,
        'hari' => 'Senin',
        'status' => 'Aktif',
    ]);

    $this->get(route('cms.jadwal', ['hari' => 'Senin', 'status' => 'Aktif']))
        ->assertOk()
        ->assertSee('hari=Senin', false)
        ->assertSee('status=Aktif', false);
});

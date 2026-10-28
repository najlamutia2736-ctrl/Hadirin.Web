<?php

use App\Models\Absensi;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\SesiAbsensi;
use App\Models\User;
use App\Services\SesiAbsensiService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Mahasiswa aktif pada kelas tertentu, lengkap dengan akun penggunanya.
 */
function mahasiswaPindai(string $namaKelas = 'Kelas RPL', array $atributMahasiswa = []): Mahasiswa
{
    $user = User::factory()->create(['role' => 'Mahasiswa', 'status' => 'Aktif']);

    return Mahasiswa::factory()->create([
        'user_id' => $user->id,
        'kelas' => $namaKelas,
        ...$atributMahasiswa,
    ]);
}

/**
 * Jadwal aktif hari ini yang rentangnya mencakup seluruh hari, jadi sesi yang
 * dibuat darinya selalu berstatus berjalan tanpa peduli jam berapa tes ini
 * dijalankan.
 */
function jadwalPindai(string $namaKelas, array $atributJadwal = []): Jadwal
{
    $kelas = Kelas::query()->where('nama_kelas', $namaKelas)->first()
        ?? Kelas::factory()->create(['nama_kelas' => $namaKelas]);

    return Jadwal::create([
        'kelas_id' => $kelas->getKey(),
        'dosen_id' => Dosen::factory()->create()->getKey(),
        'mata_kuliah' => 'Informatika',
        'hari' => SesiAbsensiService::namaHari(today()),
        'jam_mulai' => '00:00',
        'jam_selesai' => '23:59',
        'tahun_akademik' => now()->year,
        'status' => 'Aktif',
        ...$atributJadwal,
    ]);
}

/**
 * Sesi hari ini untuk satu jadwal, dibuat lewat service yang sama dengan
 * aplikasi supaya kodenya persis seperti produksi.
 */
function sesiPindai(Jadwal $jadwal): SesiAbsensi
{
    return app(SesiAbsensiService::class)
        ->pastikanSesi(collect([$jadwal]), today())
        ->firstWhere('jadwal_id', $jadwal->getKey());
}

/**
 * Sesi yang sudah lewat batas waktunya, jadi seluruh scan harus ditolak.
 */
function sesiLewat(Jadwal $jadwal): SesiAbsensi
{
    return SesiAbsensi::create([
        'jadwal_id' => $jadwal->getKey(),
        'kode_sesi' => 'UJI-LEWAT-'.$jadwal->getKey(),
        'waktu_mulai' => now()->subHours(2),
        'waktu_selesai' => now()->subMinutes(10),
        'status' => 'selesai',
    ]);
}

/**
 * Sesi yang jam mulainya masih di masa depan.
 */
function sesiBelumDimulai(Jadwal $jadwal): SesiAbsensi
{
    return SesiAbsensi::create([
        'jadwal_id' => $jadwal->getKey(),
        'kode_sesi' => 'UJI-BELUM-'.$jadwal->getKey(),
        'waktu_mulai' => now()->addMinutes(30),
        'waktu_selesai' => now()->addHours(2),
        'status' => 'aktif',
    ]);
}

test('halaman pindai hanya bisa dibuka oleh dosen dan admin', function () {
    $this->get(route('cms.pindai'))
        ->assertRedirect(route('login'));

    $this->actingAs(mahasiswaPindai()->user)
        ->get(route('cms.pindai'))
        ->assertForbidden();

    loginAdmin();

    $this->get(route('cms.pindai'))
        ->assertOk()
        ->assertSee('Pindai Barcode Absensi');
});

test('halaman pindai menampilkan sesi hari ini beserta jumlah yang tercatat', function () {
    $mahasiswa = mahasiswaPindai('Kelas RPL');
    sesiPindai(jadwalPindai('Kelas RPL', ['mata_kuliah' => 'Informatika']));

    loginAdmin();

    $this->get(route('cms.pindai'))
        ->assertOk()
        ->assertSee('Informatika')
        ->assertSee($mahasiswa->kelas)
        ->assertSee('tercatat');
});

test('tab qr sesi menampilkan qr untuk dipindai mahasiswa, tab pindai berisi kamera', function () {
    mahasiswaPindai('Kelas RPL');
    $sesi = sesiPindai(jadwalPindai('Kelas RPL'));

    loginAdmin();

    // Tab pindai (default) berisi kamera pemindai dan hasil scan, bukan QR sesi.
    $this->get(route('cms.pindai'))
        ->assertOk()
        ->assertSee('Hasil Pemindaian', false)
        ->assertDontSee('data-barcode-teks="'.$sesi->kode_sesi.'"', false);

    // QR sesi pindah ke tab-nya sendiri, supaya tampilan besar untuk mahasiswa.
    $this->get(route('cms.pindai', ['tab' => 'qr']))
        ->assertOk()
        ->assertSee('QR Sesi untuk Mahasiswa', false)
        // Isi QR-nya adalah kode sesi yang sama dengan yang dipakai alur scan mahasiswa.
        ->assertSee('data-barcode-teks="'.$sesi->kode_sesi.'"', false);
});

test('dosen bisa membuka tab qr sesi dengan sesi yang diajarinya', function () {
    $dosenLogin = loginDosen();
    $dosen = Dosen::query()->where('user_id', $dosenLogin->getKey())->firstOrFail();

    $sesi = sesiPindai(jadwalPindai('Kelas RPL', ['dosen_id' => $dosen->getKey()]));

    $this->get(route('dosen.pindai', ['tab' => 'qr']))
        ->assertOk()
        ->assertSee('QR Sesi untuk Mahasiswa', false)
        ->assertSee('data-barcode-teks="'.$sesi->kode_sesi.'"', false);

    // Tab pindai dosen tetap memunculkan kamera, bukan QR sesi.
    $this->get(route('dosen.pindai'))
        ->assertOk()
        ->assertSee('Hasil Pemindaian', false)
        ->assertDontSee('data-barcode-teks="'.$sesi->kode_sesi.'"', false);
});

test('halaman pindai dosen hanya menampilkan jadwal yang diajarinya', function () {
    $dosenLogin = loginDosen();
    $dosen = Dosen::query()->where('user_id', $dosenLogin->getKey())->firstOrFail();

    jadwalPindai('Kelas RPL', ['dosen_id' => $dosen->getKey()]);
    jadwalPindai('Kelas TI', ['mata_kuliah' => 'Matematika']);

    $this->get(route('dosen.pindai'))
        ->assertOk()
        ->assertSee('Informatika')
        ->assertDontSee('Matematika');
});

test('admin memindai barcode mahasiswa dan kehadirannya tercatat', function () {
    $mahasiswa = mahasiswaPindai('Kelas RPL', ['nim' => '20260001']);
    $sesi = sesiPindai(jadwalPindai('Kelas RPL'));

    loginAdmin();

    $this->postJson(route('cms.pindai.store'), [
        'kode' => $mahasiswa->nim,
        'sesi' => $sesi->getKey(),
    ])
        ->assertOk()
        ->assertJsonPath('sukses', true)
        ->assertJsonPath('nim', $mahasiswa->nim)
        ->assertJsonPath('mahasiswa', $mahasiswa->user->name);

    $this->assertDatabaseHas('absensis', [
        'mahasiswa_id' => $mahasiswa->getKey(),
        'sesi_absensi_id' => $sesi->getKey(),
        'status' => 'hadir',
    ]);
});

test('hasil scan dengan karakter pemisah tetap dikenali', function () {
    $mahasiswa = mahasiswaPindai('Kelas RPL', ['nim' => '20260001']);
    $sesi = sesiPindai(jadwalPindai('Kelas RPL'));

    loginAdmin();

    $this->postJson(route('cms.pindai.store'), [
        'kode' => ' 2026-0001 ',
        'sesi' => $sesi->getKey(),
    ])
        ->assertOk()
        ->assertJsonPath('sukses', true);

    $this->assertDatabaseCount('absensis', 1);
});

test('scan kedua pada sesi sama ditolak supaya kehadiran tidak ganda', function () {
    $mahasiswa = mahasiswaPindai('Kelas RPL');
    $sesi = sesiPindai(jadwalPindai('Kelas RPL'));

    loginAdmin();

    $this->postJson(route('cms.pindai.store'), [
        'kode' => $mahasiswa->nim,
        'sesi' => $sesi->getKey(),
    ])->assertOk();

    $respons = $this->postJson(route('cms.pindai.store'), [
        'kode' => $mahasiswa->nim,
        'sesi' => $sesi->getKey(),
    ])->assertStatus(422);

    expect($respons->json('pesan'))->toContain('sudah tercatat');

    $this->assertDatabaseCount('absensis', 1);
});

test('scan setelah sesi selesai ditolak sebagai absensi kedaluwarsa', function () {
    $mahasiswa = mahasiswaPindai('Kelas RPL');
    $sesi = sesiLewat(jadwalPindai('Kelas RPL'));

    loginAdmin();

    $respons = $this->postJson(route('cms.pindai.store'), [
        'kode' => $mahasiswa->nim,
        'sesi' => $sesi->getKey(),
    ])->assertStatus(422);

    expect($respons->json('pesan'))->toContain('kedaluwarsa');

    $this->assertDatabaseCount('absensis', 0);
});

test('scan sebelum sesi dimulai ditolak', function () {
    $mahasiswa = mahasiswaPindai('Kelas RPL');
    $sesi = sesiBelumDimulai(jadwalPindai('Kelas RPL'));

    loginAdmin();

    $respons = $this->postJson(route('cms.pindai.store'), [
        'kode' => $mahasiswa->nim,
        'sesi' => $sesi->getKey(),
    ])->assertStatus(422);

    expect($respons->json('pesan'))->toContain('belum dimulai');

    $this->assertDatabaseCount('absensis', 0);
});

test('barcode mahasiswa kelas lain ditolak untuk sesi ini', function () {
    $mahasiswa = mahasiswaPindai('Kelas RPL', ['nim' => '20260002']);
    $sesi = sesiPindai(jadwalPindai('Kelas TI'));

    loginAdmin();

    $respons = $this->postJson(route('cms.pindai.store'), [
        'kode' => $mahasiswa->nim,
        'sesi' => $sesi->getKey(),
    ])->assertStatus(422);

    expect($respons->json('pesan'))->toContain('bukan kelas Kelas TI');

    $this->assertDatabaseCount('absensis', 0);
});

test('barcode yang tidak terdaftar ditolak', function () {
    $sesi = sesiPindai(jadwalPindai('Kelas RPL'));

    loginAdmin();

    $respons = $this->postJson(route('cms.pindai.store'), [
        'kode' => '99999999',
        'sesi' => $sesi->getKey(),
    ])->assertStatus(422);

    expect($respons->json('pesan'))->toContain('tidak dikenal');

    $this->assertDatabaseCount('absensis', 0);
});

test('mahasiswa berstatus bukan aktif tidak dicatat kehadirannya', function () {
    $mahasiswa = mahasiswaPindai('Kelas RPL', ['status' => 'Nonaktif']);
    $sesi = sesiPindai(jadwalPindai('Kelas RPL'));

    loginAdmin();

    $respons = $this->postJson(route('cms.pindai.store'), [
        'kode' => $mahasiswa->nim,
        'sesi' => $sesi->getKey(),
    ])->assertStatus(422);

    expect($respons->json('pesan'))->toContain('bukan Aktif');

    $this->assertDatabaseCount('absensis', 0);
});

test('dosen bisa memindai jadwal yang diajarnya sendiri', function () {
    $dosenLogin = loginDosen();
    $dosen = Dosen::query()->where('user_id', $dosenLogin->getKey())->firstOrFail();

    $mahasiswa = mahasiswaPindai('Kelas RPL');
    $sesi = sesiPindai(jadwalPindai('Kelas RPL', ['dosen_id' => $dosen->getKey()]));

    $this->postJson(route('dosen.pindai.store'), [
        'kode' => $mahasiswa->nim,
        'sesi' => $sesi->getKey(),
    ])
        ->assertOk()
        ->assertJsonPath('sukses', true);

    $this->assertDatabaseCount('absensis', 1);
});

test('dosen lain ditolak memindai sesi yang bukan jadwalnya', function () {
    $mahasiswa = mahasiswaPindai('Kelas RPL');
    $sesi = sesiPindai(jadwalPindai('Kelas RPL'));

    $dosenLain = User::factory()->create(['role' => 'Dosen', 'status' => 'Aktif']);
    Dosen::factory()->create(['user_id' => $dosenLain->getKey()]);

    $this->actingAs($dosenLain)
        ->postJson(route('dosen.pindai.store'), [
            'kode' => $mahasiswa->nim,
            'sesi' => $sesi->getKey(),
        ])
        ->assertStatus(403);

    $this->assertDatabaseCount('absensis', 0);
});

test('pemindaian lewat akun mahasiswa ditolak oleh role middleware', function () {
    $mahasiswa = mahasiswaPindai('Kelas RPL');
    $sesi = sesiPindai(jadwalPindai('Kelas RPL'));

    $this->actingAs($mahasiswa->user)
        ->postJson(route('cms.pindai.store'), [
            'kode' => $mahasiswa->nim,
            'sesi' => $sesi->getKey(),
        ])
        ->assertForbidden();

    $this->assertDatabaseCount('absensis', 0);
});

test('mahasiswa tercatat di dua sesi mata kuliah yang berbeda', function () {
    $mahasiswa = mahasiswaPindai('Kelas RPL');

    $sesiPertama = sesiPindai(jadwalPindai('Kelas RPL'));
    $sesiKedua = sesiPindai(jadwalPindai('Kelas RPL', ['mata_kuliah' => 'Fisika']));

    loginAdmin();

    foreach ([$sesiPertama, $sesiKedua] as $sesi) {
        $this->postJson(route('cms.pindai.store'), [
            'kode' => $mahasiswa->nim,
            'sesi' => $sesi->getKey(),
        ])->assertOk();
    }

    $this->assertDatabaseCount('absensis', 2);

    expect(Absensi::query()->where('status', 'hadir')->count())->toBe(2);
});

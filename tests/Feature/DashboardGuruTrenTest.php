<?php

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\SesiAbsensi;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/*
| Tren bulanan dashboard guru harus memakai data `absensis`, bukan angka
| placeholder. Vanilla punya data placeholder 92/95/89/... sehingga grafik
| selalu penuh walau belum ada satu pun absensi.
*/
uses(RefreshDatabase::class);

function guruDenganKelas(): array
{
    $user = User::factory()->create(['name' => 'Guru Uji', 'role' => 'Guru', 'status' => 'Aktif']);
    $guru = Guru::factory()->create(['user_id' => $user->id]);
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X.1', 'status' => 'Aktif']);
    $kelas->guru()->sync([$guru->id]);

    return [$guru, $kelas];
}

function catat(Siswa $siswa, string $waktu, string $status): void
{
    $momen = now()->parse($waktu);

    $sesi = SesiAbsensi::firstOrCreate(
        ['kode_sesi' => 'SESI-'.$momen->format('Ymd')],
        ['waktu_mulai' => $momen->startOfDay(), 'waktu_selesai' => $momen->endOfDay(), 'status' => 'selesai'],
    );

    Absensi::create([
        'siswa_id' => $siswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => $momen,
        'status' => $status,
    ]);
}

test('dashboard guru tidak lagi menampilkan tren bulanan palsu', function () {
    [$guru] = guruDenganKelas();

    $this->actingAs($guru->user)
        ->get(route('guru.dashboard'))
        ->assertOk()
        // Angka placeholder yang dulu ditulis di Blade.
        ->assertDontSee('"month":"Jan","hadir":92')
        ->assertDontSee('"hadir":92')
        // Judul grafik menyesuaikan keadaan sebenarnya.
        ->assertSee('Menunggu absensi pertama')
        ->assertSee('Belum ada catatan absensi untuk kelas yang Anda ampu');
});

test('grafik tren tidak digambar saat belum ada absensi', function () {
    [$guru] = guruDenganKelas();

    $html = $this->actingAs($guru->user)->get(route('guru.dashboard'))->assertOk()->getContent();

    // Canvas hanya ada kalau ada data; kalau tidak, kondisi kosong yang tampil.
    expect($html)->not->toContain('id="trenChart"')
        // Kerangka 12 bulan tetap ada supaya bentuk grafiknya terlihat.
        ->toContain('border-dashed border-gray-200');
});

test('tren bulanan dirender begitu ada absensi tercatat', function () {
    [$guru, $kelas] = guruDenganKelas();
    $user = User::factory()->create(['role' => 'Siswa', 'status' => 'Aktif']);
    $siswa = Siswa::factory()->create(['user_id' => $user->id, 'kelas' => $kelas->nama_kelas]);

    catat($siswa, 'today 07:00', 'hadir');
    catat($siswa, 'today 07:05', 'hadir');
    catat($siswa, 'today 07:10', 'izin');
    catat($siswa, 'today 07:15', 'sakit');
    catat($siswa, 'today 07:20', 'alpha');

    $html = $this->actingAs($guru->user)->get(route('guru.dashboard'))->assertOk()->getContent();

    expect($html)->toContain('id="trenChart"')
        ->not->toContain('Belum ada catatan absensi untuk kelas yang Anda ampu')
        ->not->toContain('Menunggu absensi pertama');

    // Kelima catatan masuk ke bulan berjalan: 2 hadir, sisanya satu per status.
    preg_match('/const DATA_TREN_BULANAN = (\[.*?\]);/s', $html, $m);
    $tren = json_decode($m[1], true);

    expect($tren)->toHaveCount(12);

    $bulanIni = collect($tren)->last();

    expect($bulanIni['hadir'])->toBe(2)
        ->and($bulanIni['izin'])->toBe(1)
        ->and($bulanIni['sakit'])->toBe(1)
        ->and($bulanIni['alpa'])->toBe(1)
        ->and($bulanIni['total'])->toBe(5);
});

test('absensi kelas orang lain tidak ikut dihitung di tren guru', function () {
    [$guru, $kelas] = guruDenganKelas();
    $kelasLain = Kelas::factory()->create(['nama_kelas' => 'X.9', 'status' => 'Aktif']);

    $user = User::factory()->create(['role' => 'Siswa', 'status' => 'Aktif']);
    $siswaMilik = Siswa::factory()->create(['user_id' => $user->id, 'kelas' => $kelas->nama_kelas]);
    $siswaLain = Siswa::factory()->create(['user_id' => $user->id, 'kelas' => $kelasLain->nama_kelas]);

    catat($siswaMilik, 'today 07:00', 'hadir');
    catat($siswaLain, 'today 07:05', 'hadir');

    $html = $this->actingAs($guru->user)->get(route('guru.dashboard'))->assertOk()->getContent();

    preg_match('/const DATA_TREN_BULANAN = (\[.*?\]);/s', $html, $m);
    $bulanIni = collect(json_decode($m[1], true))->last();

    expect($bulanIni['total'])->toBe(1);
});

test('tren guru tanpa kelas tidak menyebabkan error', function () {
    $user = User::factory()->create(['role' => 'Guru', 'status' => 'Aktif']);
    Guru::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('guru.dashboard'))
        ->assertOk()
        ->assertSee('Menunggu absensi pertama')
        ->assertDontSee('id="trenChart"');
});

test('legenda grafik tren memuat keempat status', function () {
    [$guru, $kelas] = guruDenganKelas();
    $user = User::factory()->create(['role' => 'Siswa', 'status' => 'Aktif']);
    $siswa = Siswa::factory()->create(['user_id' => $user->id, 'kelas' => $kelas->nama_kelas]);

    catat($siswa, 'today 07:00', 'hadir');

    $html = $this->actingAs($guru->user)->get(route('guru.dashboard'))->assertOk()->getContent();

    foreach (['Hadir', 'Izin', 'Sakit', 'Alpa'] as $status) {
        expect($html)->toContain($status);
    }
});

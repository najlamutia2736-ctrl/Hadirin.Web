<?php

use App\Models\Absensi;
use App\Models\Dosen;
use App\Models\Kelas;
use App\Models\SesiAbsensi;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/*
| Tren bulanan dashboard dosen harus memakai data `absensis`, bukan angka
| placeholder. Vanilla punya data placeholder 92/95/89/... sehingga grafik
| selalu penuh walau belum ada satu pun absensi.
*/
uses(RefreshDatabase::class);

function dosenDenganKelas(): array
{
    $user = User::factory()->create(['name' => 'Dosen Uji', 'role' => 'Dosen', 'status' => 'Aktif']);
    $dosen = Dosen::factory()->create(['user_id' => $user->id]);
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    return [$dosen, $kelas];
}

function catat(Mahasiswa $mahasiswa, string $waktu, string $status): void
{
    $momen = now()->parse($waktu);

    $sesi = SesiAbsensi::firstOrCreate(
        ['kode_sesi' => 'SESI-'.$momen->format('Ymd')],
        ['waktu_mulai' => $momen->startOfDay(), 'waktu_selesai' => $momen->endOfDay(), 'status' => 'selesai'],
    );

    Absensi::create([
        'mahasiswa_id' => $mahasiswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => $momen,
        'status' => $status,
    ]);
}

test('dashboard dosen tidak lagi menampilkan tren bulanan palsu', function () {
    [$dosen] = dosenDenganKelas();

    $this->actingAs($dosen->user)
        ->get(route('dosen.dashboard'))
        ->assertOk()
        // Angka placeholder yang dulu ditulis di Blade.
        ->assertDontSee('"month":"Jan","hadir":92')
        ->assertDontSee('"hadir":92')
        // Judul grafik menyesuaikan keadaan sebenarnya.
        ->assertSee('Menunggu presensi pertama')
        ->assertSee('Belum ada catatan presensi untuk kelas yang Anda ampu');
});

test('grafik tren tidak digambar saat belum ada absensi', function () {
    [$dosen] = dosenDenganKelas();

    $html = $this->actingAs($dosen->user)->get(route('dosen.dashboard'))->assertOk()->getContent();

    // Canvas hanya ada kalau ada data; kalau tidak, kondisi kosong yang tampil.
    expect($html)->not->toContain('id="trenChart"')
        // Kerangka 12 bulan tetap ada supaya bentuk grafiknya terlihat.
        ->toContain('border-dashed border-gray-200');
});

test('tren bulanan dirender begitu ada absensi tercatat', function () {
    [$dosen, $kelas] = dosenDenganKelas();
    $user = User::factory()->create(['role' => 'Mahasiswa', 'status' => 'Aktif']);
    $mahasiswa = Mahasiswa::factory()->create(['user_id' => $user->id, 'kelas' => $kelas->nama_kelas]);

    catat($mahasiswa, 'today 07:00', 'hadir');
    catat($mahasiswa, 'today 07:05', 'hadir');
    catat($mahasiswa, 'today 07:10', 'izin');
    catat($mahasiswa, 'today 07:15', 'sakit');
    catat($mahasiswa, 'today 07:20', 'alpha');

    $html = $this->actingAs($dosen->user)->get(route('dosen.dashboard'))->assertOk()->getContent();

    expect($html)->toContain('id="trenChart"')
        ->not->toContain('Belum ada catatan presensi untuk kelas yang Anda ampu')
        ->not->toContain('Menunggu presensi pertama');

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

test('absensi kelas orang lain tidak ikut dihitung di tren dosen', function () {
    [$dosen, $kelas] = dosenDenganKelas();
    $kelasLain = Kelas::factory()->create(['nama_kelas' => 'Kelas Z', 'status' => 'Aktif']);

    $user = User::factory()->create(['role' => 'Mahasiswa', 'status' => 'Aktif']);
    $mahasiswaMilik = Mahasiswa::factory()->create(['user_id' => $user->id, 'kelas' => $kelas->nama_kelas]);
    $mahasiswaLain = Mahasiswa::factory()->create(['user_id' => $user->id, 'kelas' => $kelasLain->nama_kelas]);

    catat($mahasiswaMilik, 'today 07:00', 'hadir');
    catat($mahasiswaLain, 'today 07:05', 'hadir');

    $html = $this->actingAs($dosen->user)->get(route('dosen.dashboard'))->assertOk()->getContent();

    preg_match('/const DATA_TREN_BULANAN = (\[.*?\]);/s', $html, $m);
    $bulanIni = collect(json_decode($m[1], true))->last();

    expect($bulanIni['total'])->toBe(1);
});

test('tren dosen tanpa kelas tidak menyebabkan error', function () {
    $user = User::factory()->create(['role' => 'Dosen', 'status' => 'Aktif']);
    Dosen::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('dosen.dashboard'))
        ->assertOk()
        ->assertSee('Menunggu presensi pertama')
        ->assertDontSee('id="trenChart"');
});

test('legenda grafik tren memuat keempat status', function () {
    [$dosen, $kelas] = dosenDenganKelas();
    $user = User::factory()->create(['role' => 'Mahasiswa', 'status' => 'Aktif']);
    $mahasiswa = Mahasiswa::factory()->create(['user_id' => $user->id, 'kelas' => $kelas->nama_kelas]);

    catat($mahasiswa, 'today 07:00', 'hadir');

    $html = $this->actingAs($dosen->user)->get(route('dosen.dashboard'))->assertOk()->getContent();

    foreach (['Hadir', 'Izin', 'Sakit', 'Alpa'] as $status) {
        expect($html)->toContain($status);
    }
});

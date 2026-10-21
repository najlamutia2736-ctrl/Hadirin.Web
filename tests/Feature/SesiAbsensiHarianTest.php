<?php

use App\Models\Jadwal;
use App\Models\SesiAbsensi;
use App\Services\SesiAbsensiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

/*
| Presensi dihitung per mata kuliah, jadi sesi dibuat dari jadwal mengajar:
| satu jadwal aktif pada satu hari menghasilkan satu sesi, lengkap dengan jam
| mulai dan jam selesai miliknya sendiri. Test di sini menjaga command
| `sesi:absensi`, service pembuatnya, dan sifat idempoten pembuatannya supaya
| tidak ada mahasiswa yang dobel absen hanya karena scheduler jalan dua kali.
*/

test('command membuat satu sesi per jadwal aktif hari ini', function () {
    $jadwal = Jadwal::factory()->create([
        'hari' => SesiAbsensiService::namaHari(today()),
        'jam_mulai' => now()->subHour()->format('H:i'),
        'jam_selesai' => now()->addHour()->format('H:i'),
    ]);

    $this->artisan('sesi:absensi')->assertSuccessful();

    $sesi = SesiAbsensi::query()->firstOrFail();

    expect(SesiAbsensi::query()->count())->toBe(1)
        ->and($sesi->jadwal_id)->toBe($jadwal->id)
        ->and($sesi->kode_sesi)->toBe('JW'.$jadwal->id.'-'.today()->format('Y-m-d'))
        ->and($sesi->waktu_mulai->format('H:i'))->toBe($jadwal->jam_mulai)
        ->and($sesi->waktu_selesai->format('H:i'))->toBe($jadwal->jam_selesai)
        ->and($sesi->status)->toBe('aktif');
});

test('command membuat sesi terpisah untuk tiap jadwal hari ini', function () {
    $pagi = Jadwal::factory()->create([
        'hari' => SesiAbsensiService::namaHari(today()),
        'jam_mulai' => '07:00',
        'jam_selesai' => '08:30',
    ]);
    $siang = Jadwal::factory()->create([
        'hari' => SesiAbsensiService::namaHari(today()),
        'jam_mulai' => '09:00',
        'jam_selesai' => '10:30',
    ]);

    $this->artisan('sesi:absensi')->assertSuccessful();

    $daftar = SesiAbsensi::query()->orderBy('waktu_mulai')->get();

    expect($daftar)->toHaveCount(2)
        ->and($daftar->pluck('jadwal_id')->all())->toBe([$pagi->id, $siang->id]);
});

test('command mengabaikan jadwal hari lain dan jadwal nonaktif', function () {
    Jadwal::factory()->create([
        'hari' => SesiAbsensiService::namaHari(today()->addDay()),
    ]);
    Jadwal::factory()->create([
        'hari' => SesiAbsensiService::namaHari(today()),
        'status' => 'Nonaktif',
    ]);

    $this->artisan('sesi:absensi')->assertSuccessful();

    expect(SesiAbsensi::query()->count())->toBe(0);
});

test('command dijalankan dua kali tidak menggandakan sesi', function () {
    Jadwal::factory()->create([
        'hari' => SesiAbsensiService::namaHari(today()),
        'jam_mulai' => '07:00',
        'jam_selesai' => '08:30',
    ]);

    $this->artisan('sesi:absensi')->assertSuccessful();
    $this->artisan('sesi:absensi')->assertSuccessful();

    expect(SesiAbsensi::query()->count())->toBe(1);
});

test('command bisa membuat sesi untuk tanggal lain', function () {
    $besok = today()->addDay();

    Jadwal::factory()->create([
        'hari' => SesiAbsensiService::namaHari($besok),
    ]);

    $this->artisan('sesi:absensi', ['--tanggal' => $besok->toDateString()])
        ->assertSuccessful();

    expect(SesiAbsensi::query()->whereDate('waktu_mulai', $besok->toDateString())->exists())
        ->toBeTrue()
        ->and(SesiAbsensi::query()->whereDate('waktu_mulai', today()->toDateString())->exists())
        ->toBeFalse();
});

test('command menolak tanggal yang tidak valid', function () {
    $this->artisan('sesi:absensi', ['--tanggal' => 'nanti-saja'])
        ->assertFailed();

    expect(SesiAbsensi::query()->count())->toBe(0);
});

test('sesi melewati tengah malam berakhir pada hari berikutnya', function () {
    Jadwal::factory()->create([
        'hari' => SesiAbsensiService::namaHari(today()),
        'jam_mulai' => '20:00',
        'jam_selesai' => '02:00',
    ]);

    $sesi = app(SesiAbsensiService::class)->pastikanSesiHariIni()->firstOrFail();

    expect($sesi->waktu_mulai->format('H:i'))->toBe('20:00')
        ->and($sesi->waktu_selesai->format('H:i'))->toBe('02:00')
        ->and($sesi->waktu_selesai->toDateString())->toBe(today()->addDay()->toDateString());
});

test('command menutup sesi yang rentangnya sudah lewat', function () {
    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'LAMA',
        'waktu_mulai' => now()->subDay()->setTime(6, 0),
        'waktu_selesai' => now()->subDay()->setTime(21, 0),
        'status' => 'aktif',
    ]);

    $this->artisan('sesi:absensi')->assertSuccessful();

    expect($sesi->fresh()->status)->toBe('selesai');
});

test('sesi yang sudah selesai tidak dihitung ulang saat ditutup', function () {
    SesiAbsensi::create([
        'kode_sesi' => 'LAMA',
        'waktu_mulai' => now()->subDay()->setTime(6, 0),
        'waktu_selesai' => now()->subDay()->setTime(21, 0),
        'status' => 'selesai',
    ]);

    expect(app(SesiAbsensiService::class)->tutupSesiLewat())->toBe(0);
});

test('service mencari sesi yang sedang berjalan dari rentang waktunya', function () {
    // Sesi ini masih `aktif` padahal waktunya sudah lewat, jadi hanya boleh
    // ditemukan lewat perbandingan waktu, bukan dari kolom status.
    SesiAbsensi::create([
        'kode_sesi' => 'BASI',
        'waktu_mulai' => now()->subDay()->setTime(6, 0),
        'waktu_selesai' => now()->subDay()->setTime(21, 0),
        'status' => 'aktif',
    ]);

    $berjalan = SesiAbsensi::create([
        'kode_sesi' => 'JALAN',
        'waktu_mulai' => now()->subHour(),
        'waktu_selesai' => now()->addHour(),
        'status' => 'aktif',
    ]);

    expect(app(SesiAbsensiService::class)->sesiBerjalan()?->id)->toBe($berjalan->id);
});

test('service mengembalikan null saat belum ada sesi yang berjalan', function () {
    expect(app(SesiAbsensiService::class)->sesiBerjalan())->toBeNull();
});

test('pastikan sesi hari ini mengembalikan seluruh sesi terurut dari jam mulai', function () {
    Jadwal::factory()->create([
        'hari' => SesiAbsensiService::namaHari(today()),
        'jam_mulai' => '13:00',
        'jam_selesai' => '14:30',
    ]);
    Jadwal::factory()->create([
        'hari' => SesiAbsensiService::namaHari(today()),
        'jam_mulai' => '07:00',
        'jam_selesai' => '08:30',
    ]);

    $daftar = app(SesiAbsensiService::class)->pastikanSesiHariIni();

    expect($daftar)->toHaveCount(2)
        ->and($daftar->pluck('waktu_mulai')->map(fn ($waktu) => $waktu->format('H:i'))->all())
        ->toBe(['07:00', '13:00']);
});

test('pembuatan sesi menghormati jam jadwal, bukan jam perintah dijalankan', function () {
    // Sesi dibuat tepat di jam jadwalnya, bukan di jam ketika command jalan.
    // Kalau tidak, selisih beberapa menit membuat mahasiswa dianggap absen
    // di luar rentang sesinya sendiri.
    $jadwal = Jadwal::factory()->create([
        'hari' => SesiAbsensiService::namaHari(today()),
        'jam_mulai' => '07:00',
        'jam_selesai' => '08:30',
    ]);

    $sesi = app(SesiAbsensiService::class)->pastikanSesiHariIni()->firstOrFail();

    $waktuJadwal = today()->setTimeFromTimeString($jadwal->jam_mulai)->startOfSecond();

    expect($sesi->waktu_mulai->toDateTimeString())->toBe($waktuJadwal->toDateTimeString());
});

test('kode sesi dibuat dari jadwal dan tanggal supaya tidak pernah bentrok', function () {
    $jadwal = Jadwal::factory()->create([
        'hari' => SesiAbsensiService::namaHari(today()),
    ]);

    $sesi = app(SesiAbsensiService::class)->pastikanSesiHariIni()->firstOrFail();

    expect($sesi->kode_sesi)->toBe('JW'.$jadwal->id.'-'.Carbon::now()->format('Y-m-d'));
});

<?php

use App\Models\SesiAbsensi;
use App\Services\SesiAbsensiService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|_absensi tidak bisa dicatat tanpa sesi yang sedang berjalan, tapi tidak ada
|satu pun proses di aplikasi yang membuat sesi itu. Test di sini menjaga
|command `sesi:absensi` dan service yang dipakainya supaya sesi harian benar
|bisa dibuat, tidak_double, dan sesi yang sudah lewat ikut ditutup.
*/

test('command membuat sesi untuk hari ini dari jam buka dan jam tutup', function () {
    config()->set('sesi-absensi.jam_buka', '06:00');
    config()->set('sesi-absensi.jam_tutup', '21:00');

    $this->artisan('sesi:absensi')->assertSuccessful();

    $sesi = SesiAbsensi::query()->firstOrFail();

    expect($sesi->kode_sesi)->toBe('HARIAN-'.today()->format('Y-m-d'))
        ->and($sesi->waktu_mulai->format('H:i'))->toBe('06:00')
        ->and($sesi->waktu_selesai->format('H:i'))->toBe('21:00')
        ->and($sesi->status)->toBe('aktif');
});

test('command dijalankan dua kali hanya menghasilkan satu sesi', function () {
    $this->artisan('sesi:absensi')->assertSuccessful();
    $this->artisan('sesi:absensi')->assertSuccessful();

    expect(SesiAbsensi::query()->count())->toBe(1);
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

test('command bisa membuat sesi untuk tanggal lain', function () {
    $besok = today()->addDay();

    $this->artisan('sesi:absensi', ['--tanggal' => $besok->toDateString()])
        ->assertSuccessful();

    expect(SesiAbsensi::query()->whereDate('waktu_mulai', $besok->toDateString())->exists())
        ->toBeTrue();
});

test('command menolak tanggal yang tidak valid', function () {
    $this->artisan('sesi:absensi', ['--tanggal' => 'nanti-saja'])
        ->assertFailed();

    expect(SesiAbsensi::query()->count())->toBe(0);
});

test('sesi harian melewati tengah malam berakhir pada hari berikutnya', function () {
    config()->set('sesi-absensi.jam_buka', '20:00');
    config()->set('sesi-absensi.jam_tutup', '02:00');

    $sesi = app(SesiAbsensiService::class)->buatSesiHarian(today());

    expect($sesi->waktu_mulai->format('H:i'))->toBe('20:00')
        ->and($sesi->waktu_selesai->format('H:i'))->toBe('02:00')
        ->and($sesi->waktu_selesai->toDateString())->toBe(today()->addDay()->toDateString());
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

test('pastikan sesi hari ini memakai sesi yang sedang berjalan kalau ada', function () {
    $berjalan = SesiAbsensi::create([
        'kode_sesi' => 'JALAN',
        'waktu_mulai' => now()->subHour(),
        'waktu_selesai' => now()->addHour(),
        'status' => 'aktif',
    ]);

    expect(app(SesiAbsensiService::class)->pastikanSesiHariIni()->id)->toBe($berjalan->id)
        ->and(SesiAbsensi::query()->count())->toBe(1);
});

test('kode sesi memakai awalan dari config', function () {
    config()->set('sesi-absensi.kode_sesi', 'PAGI');

    $sesi = app(SesiAbsensiService::class)->buatSesiHarian(today());

    expect($sesi->kode_sesi)->toBe('PAGI-'.today()->format('Y-m-d'));
});

test('awalan kode sesi yang kosong tetap menghasilkan kode yang unik', function () {
    config()->set('sesi-absensi.kode_sesi', '');

    $sesi = app(SesiAbsensiService::class)->buatSesiHarian(today());

    expect($sesi->kode_sesi)->toBe('SESI-'.today()->format('Y-m-d'));
});

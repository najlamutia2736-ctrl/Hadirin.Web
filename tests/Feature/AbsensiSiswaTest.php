<?php

use App\Models\Absensi;
use App\Models\SesiAbsensi;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Siswa yang sudah punya akun-user, sesuai relasi yang dipakai halaman absensi.
 */
function siswaDenganAkun(array $atributSiswa = []): Siswa
{
    $user = User::factory()->create(['role' => 'Siswa']);

    return Siswa::factory()->create([
        'user_id' => $user->id,
        ...$atributSiswa,
    ]);
}

test('halaman absensi hanya bisa dibuka oleh akun siswa yang login', function () {
    $this->get(route('absensi.index'))
        ->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['role' => 'Guru']))
        ->get(route('absensi.index'))
        ->assertForbidden();

    $this->actingAs(User::factory()->create(['role' => 'Admin']))
        ->get(route('absensi.index'))
        ->assertForbidden();
});

test('halaman absensi menampilkan identitas siswa yang login', function () {
    $siswa = siswaDenganAkun([
        'nisn' => '20240101',
        'kelas' => 'XII-A',
        'jurusan' => 'RPL',
    ]);

    $this->actingAs($siswa->user)
        ->get(route('absensi.index'))
        ->assertOk()
        ->assertSee($siswa->user->name)
        ->assertSee('20240101')
        ->assertSee('XII-A')
        ->assertSee('RPL')
        ->assertSee('Identitas Siswa');
});

test('halaman absensi menampilkan status belum absen saat belum ada catatan hari ini', function () {
    $siswa = siswaDenganAkun();

    // Catatan absensi kemarin tidak boleh ikut terhitung sebagai hari ini.
    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'PAGI',
        'waktu_mulai' => now()->subDay()->setTime(7, 0),
        'waktu_selesai' => now()->subDay()->setTime(9, 0),
        'status' => 'selesai',
    ]);

    Absensi::create([
        'siswa_id' => $siswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now()->subDay()->setTime(7, 30),
        'status' => 'hadir',
    ]);

    $this->actingAs($siswa->user)
        ->get(route('absensi.index'))
        ->assertOk()
        ->assertSee('Belum Absen')
        ->assertSee('Absen Sekarang');
});

test('halaman absensi menampilkan status dan waktu saat sudah absen hari ini', function () {
    $siswa = siswaDenganAkun();

    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'PAGI',
        'waktu_mulai' => now()->setTime(7, 0),
        'waktu_selesai' => now()->setTime(9, 0),
        'status' => 'selesai',
    ]);

    Absensi::create([
        'siswa_id' => $siswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now()->setTime(7, 30),
        'status' => 'hadir',
        'keterangan' => 'Tepat waktu',
    ]);

    $this->actingAs($siswa->user)
        ->get(route('absensi.index'))
        ->assertOk()
        ->assertSee('Hadir')
        ->assertSee('07:30')
        ->assertSee('PAGI')
        ->assertSee('Tepat waktu')
        ->assertDontSee('Absen Sekarang');
});

test('rekap bulan ini menghitung hadir izin sakit dan alpha', function () {
    $siswa = siswaDenganAkun();

    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'PAGI',
        'waktu_mulai' => now()->startOfMonth()->setTime(7, 0),
        'waktu_selesai' => now()->startOfMonth()->setTime(9, 0),
        'status' => 'selesai',
    ]);

    foreach ([['hadir', 3], ['izin', 1], ['sakit', 1], ['alpha', 1]] as [$status, $jumlah]) {
        for ($i = 0; $i < $jumlah; $i++) {
            Absensi::create([
                'siswa_id' => $siswa->id,
                'sesi_absensi_id' => $sesi->id,
                'waktu_absen' => now()->startOfMonth()->addDays($i)->setTime(7, 30),
                'status' => $status,
            ]);
        }
    }

    // Bulan lalu tidak boleh ikut masuk rekap bulan ini.
    Absensi::create([
        'siswa_id' => $siswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now()->subMonthNoOverflow()->setTime(7, 30),
        'status' => 'hadir',
    ]);

    $response = $this->actingAs($siswa->user)->get(route('absensi.index'))->assertOk();

    // 3 hadir dari 6 catatan = 50%.
    $response->assertSee('50', false);
    $response->assertSee('6 hari tercatat');
});

test('menu pada halaman absensi menuju ke enam halaman lain', function () {
    $siswa = siswaDenganAkun();

    $this->actingAs($siswa->user)
        ->get(route('absensi.index'))
        ->assertOk()
        ->assertSee(route('absensi.scan-qr'), false)
        ->assertSee(route('absensi.id-unik'), false)
        ->assertSee(route('absensi.izin-sakit'), false)
        ->assertSee(route('absensi.notifikasi'), false)
        ->assertSee(route('absensi.metode'), false)
        ->assertSee(route('absensi.identitas'), false);
});

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
        'mata_pelajaran' => 'Matematika',
    ]);

    $this->actingAs($siswa->user)
        ->get(route('absensi.index'))
        ->assertOk()
        ->assertSee($siswa->user->name)
        ->assertSee('20240101')
        ->assertSee('XII-A')
        ->assertSee('Matematika')
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

test('halaman scan qr memuat kamera dan pustaka pembaca qr', function () {
    $siswa = siswaDenganAkun();

    $this->actingAs($siswa->user)
        ->get(route('absensi.scan-qr'))
        ->assertOk()
        ->assertSee('Scan QR Code')
        // Elemen yang dibaca oleh skrip.
        ->assertSee('id="kamera"', false)
        ->assertSee('id="kanvas"', false)
        ->assertSee('jsqr@1.4.0', false)
        ->assertSee('getUserMedia', false);
});

test('halaman scan qr hanya terbuka untuk akun yang sudah login', function () {
    $this->get(route('absensi.scan-qr'))
        ->assertRedirect(route('login'));
});

test('halaman metode menjelaskan keempat cara absen', function () {
    $siswa = siswaDenganAkun();

    $this->actingAs($siswa->user)
        ->get(route('absensi.metode'))
        ->assertOk()
        ->assertSee('Metode Absensi')
        ->assertSee('Scan QR Code')
        ->assertSee('Kode NISN')
        ->assertSee('Izin / Sakit')
        ->assertSee('Notifikasi')
        // Tiap metode harus punya tombol yang menuju halamannya.
        ->assertSee(route('absensi.scan-qr'), false)
        ->assertSee(route('absensi.id-unik'), false)
        ->assertSee(route('absensi.izin-sakit'), false)
        ->assertSee(route('absensi.notifikasi'), false);
});

test('halaman metode menampilkan identitas siswa yang login', function () {
    $siswa = siswaDenganAkun(['kelas' => 'XI-RPL']);

    $this->actingAs($siswa->user)
        ->get(route('absensi.metode'))
        ->assertOk()
        ->assertSee($siswa->user->name)
        ->assertSee('XI-RPL');
});

test('halaman metode menampilkan status belum absen dan menawarkan absen', function () {
    $siswa = siswaDenganAkun();

    $this->actingAs($siswa->user)
        ->get(route('absensi.metode'))
        ->assertOk()
        ->assertSee('Belum Absen')
        ->assertSee('Absen Sekarang');
});

test('halaman metode menampilkan waktu absen saat sudah absen hari ini', function () {
    $siswa = siswaDenganAkun();

    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'SORE',
        'waktu_mulai' => now()->setTime(15, 0),
        'waktu_selesai' => now()->setTime(17, 0),
        'status' => 'selesai',
    ]);

    Absensi::create([
        'siswa_id' => $siswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now()->setTime(15, 10),
        'status' => 'izin',
        'keterangan' => 'Kurang sehat',
    ]);

    $this->actingAs($siswa->user)
        ->get(route('absensi.metode'))
        ->assertOk()
        ->assertSee('Izin')
        ->assertSee('15:10')
        ->assertSee('SORE')
        ->assertDontSee('Absen Sekarang');
});

/*
| Izin dan sakit dicatat di tabel `absensis` yang sama dengan absensi biasa,
| hanya statusnya yang berbeda. Karena itu pengajuan ini otomatis ikut terlihat
| di dashboard guru dan rekap admin.
*/

function sesiBerjalan(): SesiAbsensi
{
    return SesiAbsensi::create([
        'kode_sesi' => 'PAGI',
        'waktu_mulai' => now()->subHour(),
        'waktu_selesai' => now()->addHour(),
        'status' => 'aktif',
    ]);
}

/*
| ID Unik dicatat dengan kode milik siswa yang sedang login, bukan kode yang
| dicari dari database. Kalau kodenya dicari, siapa pun bisa mengetik NISN
| temannya dan tercatat hadir untuk orang itu.
*/

test('halaman id unik menampilkan formulir dan kode kartu siswa', function () {
    $siswa = siswaDenganAkun(['nisn' => '20240101', 'kelas' => 'XI-RPL']);

    $this->actingAs($siswa->user)
        ->get(route('absensi.id-unik'))
        ->assertOk()
        ->assertSee('Absen Kode NISN')
        ->assertSee('Masukkan Kode')
        ->assertSee('Kartu Absensi')
        ->assertSee('20240101')
        ->assertSee('XI-RPL')
        ->assertSee($siswa->user->name)
        ->assertSee(route('absensi.id-unik.store'), false);
});

test('halaman id unik hanya untuk akun siswa', function () {
    $this->get(route('absensi.id-unik'))
        ->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['role' => 'Guru']))
        ->get(route('absensi.id-unik'))
        ->assertForbidden();
});

test('kode id unik yang benar mencatat kehadiran sebagai hadir', function () {
    $siswa = siswaDenganAkun(['nisn' => '20240101']);
    $sesi = sesiBerjalan();

    $this->actingAs($siswa->user)
        ->post(route('absensi.id-unik.store'), ['kode' => '20240101'])
        ->assertRedirect(route('absensi.id-unik'))
        ->assertSessionHas('sukses');

    $absensi = Absensi::where('siswa_id', $siswa->id)->firstOrFail();

    expect($absensi->status)->toBe('hadir')
        ->and($absensi->sesi_absensi_id)->toBe($sesi->id);
});

test('kode id unik diterima walau ada spasi dan tanda hubung', function () {
    $siswa = siswaDenganAkun(['nisn' => '20240101']);
    sesiBerjalan();

    $this->actingAs($siswa->user)
        ->post(route('absensi.id-unik.store'), ['kode' => '2024 0101'])
        ->assertRedirect(route('absensi.id-unik'))
        ->assertSessionHas('sukses');

    expect(Absensi::where('siswa_id', $siswa->id)->count())->toBe(1);
});

test('kode id unik milik siswa lain tidak bisa dipakai', function () {
    $siswa = siswaDenganAkun(['nisn' => '20240101']);
    $teman = siswaDenganAkun(['nisn' => '20249999']);
    sesiBerjalan();

    // Siswa mengetik NISN temannya.
    $this->actingAs($siswa->user)
        ->post(route('absensi.id-unik.store'), ['kode' => $teman->nisn])
        ->assertRedirect(route('absensi.id-unik'))
        ->assertSessionHas('gagal');

    expect(Absensi::where('siswa_id', $siswa->id)->count())->toBe(0)
        ->and(Absensi::where('siswa_id', $teman->id)->count())->toBe(0);
});

test('kode id unik kosong ditolak', function () {
    $siswa = siswaDenganAkun(['nisn' => '20240101']);
    sesiBerjalan();

    $this->actingAs($siswa->user)
        ->post(route('absensi.id-unik.store'), ['kode' => ''])
        ->assertSessionHasErrors('kode');
});

test('tidak bisa absen id unik dua kali di hari yang sama', function () {
    $siswa = siswaDenganAkun(['nisn' => '20240101']);
    $sesi = sesiBerjalan();

    Absensi::create([
        'siswa_id' => $siswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now()->setTime(7, 30),
        'status' => 'hadir',
    ]);

    $this->actingAs($siswa->user)
        ->post(route('absensi.id-unik.store'), ['kode' => '20240101'])
        ->assertRedirect(route('absensi.id-unik'))
        ->assertSessionHas('gagal');

    expect(Absensi::where('siswa_id', $siswa->id)->count())->toBe(1);
});

test('id unik ditolak saat tidak ada sesi absensi yang berjalan', function () {
    $siswa = siswaDenganAkun(['nisn' => '20240101']);

    $this->actingAs($siswa->user)
        ->post(route('absensi.id-unik.store'), ['kode' => '20240101'])
        ->assertRedirect(route('absensi.id-unik'))
        ->assertSessionHas('gagal');

    expect(Absensi::where('siswa_id', $siswa->id)->count())->toBe(0);
});

test('halaman id unik menyembunyikan formulir saat sudah absen', function () {
    $siswa = siswaDenganAkun(['nisn' => '20240101']);
    $sesi = sesiBerjalan();

    Absensi::create([
        'siswa_id' => $siswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now()->setTime(7, 30),
        'status' => 'hadir',
    ]);

    $this->actingAs($siswa->user)
        ->get(route('absensi.id-unik'))
        ->assertOk()
        ->assertSee('Kehadiran hari ini sudah tercatat')
        ->assertDontSee('Catat Kehadiran')
        ->assertDontSee(route('absensi.id-unik.store'), false);
});

test('halaman identitas menampilkan data siswa dan form ubah', function () {
    $siswa = siswaDenganAkun([
        'nisn' => '20240101',
        'kelas' => 'XII-A',
        'wali' => 'Budi Santoso',
        'telepon_wali' => '08123456789',
        'jenis_kelamin' => 'L',
    ]);

    $this->actingAs($siswa->user)
        ->get(route('absensi.identitas'))
        ->assertOk()
        ->assertSee('Identitas Siswa')
        ->assertSee('Data yang Bisa Diubah')
        ->assertSee('Data Permanen')
        ->assertSee($siswa->user->name)
        ->assertSee('20240101')
        ->assertSee('XII-A')
        ->assertSee('Budi Santoso')
        ->assertSee('08123456789')
        ->assertSee($siswa->user->email)
        ->assertSee(route('absensi.identitas.update'), false);
});

test('halaman identitas menampilkan rekap kehadiran bulan ini', function () {
    $siswa = siswaDenganAkun();
    $sesi = sesiBerjalan();

    foreach ([['hadir', 3], ['izin', 1]] as [$status, $jumlah]) {
        for ($i = 0; $i < $jumlah; $i++) {
            Absensi::create([
                'siswa_id' => $siswa->id,
                'sesi_absensi_id' => $sesi->id,
                'waktu_absen' => now()->startOfMonth()->addDays($i)->setTime(7, 30),
                'status' => $status,
            ]);
        }
    }

    $this->actingAs($siswa->user)
        ->get(route('absensi.identitas'))
        ->assertOk()
        // 3 hadir dari 4 catatan = 75%.
        ->assertSee('75', false)
        ->assertSee('4 hari tercatat');
});

test('siswa bisa mengubah nama jenis kelamin dan kontak wali', function () {
    $siswa = siswaDenganAkun([
        'nisn' => '20240101',
        'kelas' => 'XII-A',
        'jenis_kelamin' => 'L',
    ]);

    $this->actingAs($siswa->user)
        ->post(route('absensi.identitas.update'), [
            'name' => 'Dina Fitrii',
            'gender' => 'P',
            'parent' => 'Budi Santoso',
            'phone' => '08123456789',
        ])
        ->assertRedirect(route('absensi.identitas'))
        ->assertSessionHas('sukses');

    $siswa->refresh();
    $user = $siswa->user->fresh();

    expect($siswa->jenis_kelamin)->toBe('P')
        ->and($siswa->wali)->toBe('Budi Santoso')
        ->and($siswa->telepon_wali)->toBe('08123456789')
        ->and($user->name)->toBe('Dina Fitrii');
});

/*
| NISN dan kelas menentukan absensi, jadi keduanya harus tetap milik admin.
| Route ini yang membuat halaman identitas hanya mengubah sebagian data.
*/

test('siswa tidak bisa mengubah nisn kelas maupun email lewat halaman identitas', function () {
    $siswa = siswaDenganAkun(['nisn' => '20240101', 'kelas' => 'XII-A']);
    $emailAwal = $siswa->user->email;

    // Field yang tidak ada di form tetap dikirim lewat request.
    $this->actingAs($siswa->user)
        ->post(route('absensi.identitas.update'), [
            'name' => 'Dina Fitrii',
            'gender' => 'P',
            'parent' => null,
            'phone' => null,
            'nisn' => '99999999',
            'nis' => '99999999',
            'kelas' => 'X-A',
            'class' => 'X-A',
            'email' => 'penyerang@contoh.id',
        ])
        ->assertRedirect(route('absensi.identitas'));

    $siswa->refresh();

    expect($siswa->nisn)->toBe('20240101')
        ->and($siswa->kelas)->toBe('XII-A')
        ->and($siswa->user->fresh()->email)->toBe($emailAwal);
});

test('perubahan identitas ditolak kalau nama kosong atau jenis kelamin tidak valid', function () {
    $siswa = siswaDenganAkun();

    $this->actingAs($siswa->user)
        ->post(route('absensi.identitas.update'), [
            'name' => '',
            'gender' => 'X',
        ])
        ->assertSessionHasErrors(['name', 'gender']);

    expect($siswa->user->fresh()->name)->not->toBe('');
});

test('halaman identitas hanya untuk akun siswa', function () {
    $this->get(route('absensi.identitas'))
        ->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['role' => 'Guru']))
        ->get(route('absensi.identitas'))
        ->assertForbidden();

    $this->actingAs(User::factory()->create(['role' => 'Admin']))
        ->post(route('absensi.identitas.update'), [
            'name' => 'Menyerang',
            'gender' => 'L',
        ])
        ->assertForbidden();
});

test('halaman notifikasi menampilkan riwayat rekap dan strip mingguan', function () {
    $siswa = siswaDenganAkun();
    $sesi = sesiBerjalan();

    foreach ([['hadir', 1], ['izin', 1], ['sakit', 1], ['alpha', 1]] as [$status, $jumlah]) {
        for ($i = 0; $i < $jumlah; $i++) {
            Absensi::create([
                'siswa_id' => $siswa->id,
                'sesi_absensi_id' => $sesi->id,
                'waktu_absen' => now()->startOfMonth()->addDays($i)->setTime(7, 30),
                'status' => $status,
                'keterangan' => $status === 'izin' ? 'Acara keluarga.' : null,
            ]);
        }
    }

    $this->actingAs($siswa->user)
        ->get(route('absensi.notifikasi'))
        ->assertOk()
        ->assertSee('Notifikasi Absensi')
        ->assertSee('7 Hari Terakhir')
        ->assertSee('Rekap Bulan Ini')
        ->assertSee('Riwayat Absensi')
        ->assertSee('4 catatan')
        // Rekap bulan ini: 1 hadir dari 4 catatan = 25%.
        ->assertSee('25', false)
        // Keterangan pada riwayat ikut tampil.
        ->assertSee('Acara keluarga.');
});

test('halaman notifikasi menampilkan alasan tiap status di rekap', function () {
    $siswa = siswaDenganAkun();

    $this->actingAs($siswa->user)
        ->get(route('absensi.notifikasi'))
        ->assertOk()
        ->assertSee('tidak dihitung sebagai alpa')
        ->assertSee('Belum ada catatan sampai batas waktu sesi berakhir.');
});

test('halaman notifikasi menampilkan daftar kosong saat belum ada riwayat', function () {
    $siswa = siswaDenganAkun();

    $this->actingAs($siswa->user)
        ->get(route('absensi.notifikasi'))
        ->assertOk()
        ->assertSee('Belum ada riwayat')
        ->assertSee('Mulai absen');
});

test('halaman notifikasi menampilkan status hari ini beserta waktu dan sesi', function () {
    $siswa = siswaDenganAkun();
    $sesi = sesiBerjalan();

    Absensi::create([
        'siswa_id' => $siswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now()->setTime(7, 30),
        'status' => 'hadir',
    ]);

    $this->actingAs($siswa->user)
        ->get(route('absensi.notifikasi'))
        ->assertOk()
        ->assertSee('Hadir')
        ->assertSee('07:30')
        ->assertSee('PAGI')
        ->assertDontSee('Absen Sekarang');
});

test('halaman notifikasi menampilkan tujuh hari terakhir dengan hari kosong', function () {
    $siswa = siswaDenganAkun();
    $sesi = sesiBerjalan();

    // Hanya dua hari yang punya catatan; lima hari sisanya harus tetap muncul
    // sebagai kosong, bukan hilang dari strip mingguan.
    foreach ([1, 3] as $mundur) {
        Absensi::create([
            'siswa_id' => $siswa->id,
            'sesi_absensi_id' => $sesi->id,
            'waktu_absen' => now()->subDays($mundur)->setTime(7, 30),
            'status' => 'hadir',
        ]);
    }

    $this->actingAs($siswa->user)
        ->get(route('absensi.notifikasi'))
        ->assertOk()
        // Rentang 7 hari: 6 hari lalu sampai hari ini.
        ->assertSee(now()->subDays(6)->translatedFormat('d M'))
        ->assertSee(now()->translatedFormat('d M Y'))
        ->assertSee('2 catatan');
});

test('halaman notifikasi hanya untuk akun siswa', function () {
    $this->get(route('absensi.notifikasi'))
        ->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['role' => 'Guru']))
        ->get(route('absensi.notifikasi'))
        ->assertForbidden();
});

test('halaman izin sakit menampilkan formulir dan identitas siswa', function () {
    $siswa = siswaDenganAkun(['kelas' => 'XI-RPL']);

    $this->actingAs($siswa->user)
        ->get(route('absensi.izin-sakit'))
        ->assertOk()
        ->assertSee('Izin / Sakit')
        ->assertSee('Formulir Pengajuan')
        ->assertSee($siswa->user->name)
        ->assertSee('XI-RPL')
        ->assertSee(route('absensi.izin-sakit.store'), false)
        // Dua jenis pengajuan harus selectable.
        ->assertSee('value="izin"', false)
        ->assertSee('value="sakit"', false);
});

test('halaman izin sakit hanya untuk akun siswa', function () {
    $this->get(route('absensi.izin-sakit'))
        ->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['role' => 'Guru']))
        ->get(route('absensi.izin-sakit'))
        ->assertForbidden();
});

test('pengajuan izin tersimpan sebagai absensi berstatus izin', function () {
    $siswa = siswaDenganAkun();
    $sesi = sesiBerjalan();

    $this->actingAs($siswa->user)
        ->post(route('absensi.izin-sakit.store'), [
            'jenis' => 'izin',
            'alasan' => 'Ada acara nikah vina di luar kota hari ini.',
            'tanggal' => now()->toDateString(),
        ])
        ->assertRedirect(route('absensi.izin-sakit'))
        ->assertSessionHas('sukses');

    $absensi = Absensi::where('siswa_id', $siswa->id)->firstOrFail();

    expect($absensi->status)->toBe('izin')
        ->and($absensi->sesi_absensi_id)->toBe($sesi->id)
        ->and($absensi->keterangan)->toContain('nikah vina');
});

test('pengajuan sakit tersimpan sebagai absensi berstatus sakit', function () {
    $siswa = siswaDenganAkun();
    sesiBerjalan();

    $this->actingAs($siswa->user)
        ->post(route('absensi.izin-sakit.store'), [
            'jenis' => 'sakit',
            'alasan' => 'Demam sejak semalam dan sudah minum obat.',
            'tanggal' => now()->toDateString(),
        ])
        ->assertRedirect(route('absensi.izin-sakit'));

    expect(Absensi::where('siswa_id', $siswa->id)->value('status'))->toBe('sakit');
});

test('pengajuan tanpa alasan yang jelas ditolak', function () {
    $siswa = siswaDenganAkun();
    sesiBerjalan();

    $this->actingAs($siswa->user)
        ->post(route('absensi.izin-sakit.store'), [
            'jenis' => 'izin',
            'alasan' => 'sakit',
            'tanggal' => now()->toDateString(),
        ])
        ->assertSessionHasErrors('alasan');

    expect(Absensi::where('siswa_id', $siswa->id)->count())->toBe(0);
});

test('jenis pengajuan di luar izin dan sakit ditolak', function () {
    $siswa = siswaDenganAkun();
    sesiBerjalan();

    $this->actingAs($siswa->user)
        ->post(route('absensi.izin-sakit.store'), [
            'jenis' => 'hadir',
            'alasan' => 'Mau absen biasa saja hari ini.',
            'tanggal' => now()->toDateString(),
        ])
        ->assertSessionHasErrors('jenis');

    expect(Absensi::where('siswa_id', $siswa->id)->count())->toBe(0);
});

test('tanggal yang sudah lewat ditolak', function () {
    $siswa = siswaDenganAkun();
    sesiBerjalan();

    $this->actingAs($siswa->user)
        ->post(route('absensi.izin-sakit.store'), [
            'jenis' => 'izin',
            'alasan' => 'Izin sudahLewat tapi tetap dikirim.',
            'tanggal' => now()->subDay()->toDateString(),
        ])
        ->assertSessionHasErrors('tanggal');
});

test('tidak bisa mengirim pengajuan kedua di hari yang sama', function () {
    $siswa = siswaDenganAkun();
    $sesi = sesiBerjalan();

    // Kehadiran sudah tercatat di pagi hari.
    Absensi::create([
        'siswa_id' => $siswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now()->setTime(7, 30),
        'status' => 'hadir',
    ]);

    $this->actingAs($siswa->user)
        ->post(route('absensi.izin-sakit.store'), [
            'jenis' => 'izin',
            'alasan' => 'Mau izin padahal sudah absen pagi tadi.',
            'tanggal' => now()->toDateString(),
        ])
        ->assertRedirect(route('absensi.izin-sakit'))
        ->assertSessionHas('gagal');

    expect(Absensi::where('siswa_id', $siswa->id)->count())->toBe(1);
});

test('pengajuan ditolak saat tidak ada sesi absensi yang berjalan', function () {
    $siswa = siswaDenganAkun();

    $this->actingAs($siswa->user)
        ->post(route('absensi.izin-sakit.store'), [
            'jenis' => 'izin',
            'alasan' => 'Izin tapi tidak ada sesi yang sedang berjalan.',
            'tanggal' => now()->toDateString(),
        ])
        ->assertRedirect(route('absensi.izin-sakit'))
        ->assertSessionHas('gagal');

    expect(Absensi::where('siswa_id', $siswa->id)->count())->toBe(0);
});

test('halaman izin sakit menampilkan riwayat pengajuan', function () {
    $siswa = siswaDenganAkun();
    $sesi = sesiBerjalan();

    Absensi::create([
        'siswa_id' => $siswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now()->subDays(3)->setTime(7, 30),
        'status' => 'sakit',
        'keterangan' => 'Demam dua hari lalu.',
    ]);

    $this->actingAs($siswa->user)
        ->get(route('absensi.izin-sakit'))
        ->assertOk()
        ->assertSee('Pengajuan Terakhir')
        ->assertSee('Demam dua hari lalu.');
});

test('halaman izin sakit menampilkan pesan saat kehadiran sudah tercatat', function () {
    $siswa = siswaDenganAkun();
    $sesi = sesiBerjalan();

    Absensi::create([
        'siswa_id' => $siswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now()->setTime(7, 30),
        'status' => 'hadir',
    ]);

    $this->actingAs($siswa->user)
        ->get(route('absensi.izin-sakit'))
        ->assertOk()
        ->assertSee('Kehadiran hari ini sudah tercatat');
});

test('halaman metode hanya bisa dibuka oleh akun siswa', function () {
    $this->get(route('absensi.metode'))
        ->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['role' => 'Guru']))
        ->get(route('absensi.metode'))
        ->assertForbidden();

    $this->actingAs(User::factory()->create(['role' => 'Admin']))
        ->get(route('absensi.metode'))
        ->assertForbidden();
});

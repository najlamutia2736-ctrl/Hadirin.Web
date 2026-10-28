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
 * Mahasiswa yang sudah punya akun-user, sesuai relasi yang dipakai halaman absensi.
 */
function mahasiswaDenganAkun(array $atributMahasiswa = []): Mahasiswa
{
    $user = User::factory()->create(['role' => 'Mahasiswa']);

    return Mahasiswa::factory()->create([
        'user_id' => $user->id,
        ...$atributMahasiswa,
    ]);
}

test('halaman absensi hanya bisa dibuka oleh akun mahasiswa yang login', function () {
    $this->get(route('absensi.index'))
        ->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['role' => 'Dosen']))
        ->get(route('absensi.index'))
        ->assertForbidden();

    $this->actingAs(User::factory()->create(['role' => 'Admin']))
        ->get(route('absensi.index'))
        ->assertForbidden();
});

test('halaman absensi menampilkan identitas mahasiswa yang login', function () {
    $mahasiswa = mahasiswaDenganAkun([
        'nim' => '20240101',
        'kelas' => 'Kelas H',
    ]);

    $this->actingAs($mahasiswa->user)
        ->get(route('absensi.index'))
        ->assertOk()
        ->assertSee($mahasiswa->user->name)
        ->assertSee('20240101')
        ->assertSee('Kelas H')
        ->assertSee('Identitas Mahasiswa');
});

test('halaman absensi menampilkan status belum absen saat belum ada catatan hari ini', function () {
    $mahasiswa = mahasiswaDenganAkun();

    // Catatan absensi kemarin tidak boleh ikut terhitung sebagai hari ini.
    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'PAGI',
        'waktu_mulai' => now()->subDay()->setTime(7, 0),
        'waktu_selesai' => now()->subDay()->setTime(9, 0),
        'status' => 'selesai',
    ]);

    Absensi::create([
        'mahasiswa_id' => $mahasiswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now()->subDay()->setTime(7, 30),
        'status' => 'hadir',
    ]);

    $this->actingAs($mahasiswa->user)
        ->get(route('absensi.index'))
        ->assertOk()
        ->assertSee('Belum Presen')
        ->assertSee('Presen Sekarang');
});

test('halaman absensi menampilkan status dan waktu saat sudah presen hari ini', function () {
    $mahasiswa = mahasiswaDenganAkun();

    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'PAGI',
        'waktu_mulai' => now()->setTime(7, 0),
        'waktu_selesai' => now()->setTime(9, 0),
        'status' => 'selesai',
    ]);

    Absensi::create([
        'mahasiswa_id' => $mahasiswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now()->setTime(7, 30),
        'status' => 'hadir',
        'keterangan' => 'Tepat waktu',
    ]);

    $this->actingAs($mahasiswa->user)
        ->get(route('absensi.index'))
        ->assertOk()
        ->assertSee('Hadir')
        ->assertSee('07:30')
        ->assertSee('PAGI')
        ->assertSee('Tepat waktu')
        ->assertDontSee('Presen Sekarang');
});

test('rekap bulan ini menghitung hadir izin sakit dan alpha', function () {
    $mahasiswa = mahasiswaDenganAkun();

    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'PAGI',
        'waktu_mulai' => now()->startOfMonth()->setTime(7, 0),
        'waktu_selesai' => now()->startOfMonth()->setTime(9, 0),
        'status' => 'selesai',
    ]);

    foreach ([['hadir', 3], ['izin', 1], ['sakit', 1], ['alpha', 1]] as [$status, $jumlah]) {
        for ($i = 0; $i < $jumlah; $i++) {
            Absensi::create([
                'mahasiswa_id' => $mahasiswa->id,
                'sesi_absensi_id' => $sesi->id,
                'waktu_absen' => now()->startOfMonth()->addDays($i)->setTime(7, 30),
                'status' => $status,
            ]);
        }
    }

    // Bulan lalu tidak boleh ikut masuk rekap bulan ini.
    Absensi::create([
        'mahasiswa_id' => $mahasiswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now()->subMonthNoOverflow()->setTime(7, 30),
        'status' => 'hadir',
    ]);

    $response = $this->actingAs($mahasiswa->user)->get(route('absensi.index'))->assertOk();

    // 3 hadir dari 6 catatan = 50%.
    $response->assertSee('50', false);
    $response->assertSee('6 catatan presensi');
});

test('rekap bulan ini dirinci per mata kuliah', function () {
    $mahasiswa = mahasiswaDenganAkun();

    $sesiInformatika = sesiUntuk($mahasiswa, ['mata_kuliah' => 'Informatika']);
    $sesiAljabar = sesiUntuk($mahasiswa, ['mata_kuliah' => 'Aljabar Linear'], 120);

    Absensi::create([
        'mahasiswa_id' => $mahasiswa->id,
        'sesi_absensi_id' => $sesiInformatika->id,
        'waktu_absen' => now(),
        'status' => 'hadir',
    ]);

    Absensi::create([
        'mahasiswa_id' => $mahasiswa->id,
        'sesi_absensi_id' => $sesiAljabar->id,
        'waktu_absen' => now(),
        'status' => 'sakit',
    ]);

    $response = $this->actingAs($mahasiswa->user)->get(route('absensi.index'))->assertOk();

    $response->assertSee('Per Mata Kuliah')
        ->assertSee('Informatika')
        ->assertSee('Aljabar Linear');
});

test('menu pada halaman absensi menuju ke enam halaman lain', function () {
    $mahasiswa = mahasiswaDenganAkun();

    $this->actingAs($mahasiswa->user)
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
    $mahasiswa = mahasiswaDenganAkun();

    $this->actingAs($mahasiswa->user)
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
    $mahasiswa = mahasiswaDenganAkun();

    $this->actingAs($mahasiswa->user)
        ->get(route('absensi.metode'))
        ->assertOk()
        ->assertSee('Metode Presensi')
        ->assertSee('Scan QR Code')
        ->assertSee('Kode NIM')
        ->assertSee('Izin / Sakit')
        ->assertSee('Notifikasi')
        // Tiap metode harus punya tombol yang menuju halamannya.
        ->assertSee(route('absensi.scan-qr'), false)
        ->assertSee(route('absensi.id-unik'), false)
        ->assertSee(route('absensi.izin-sakit'), false)
        ->assertSee(route('absensi.notifikasi'), false);
});

test('halaman metode menampilkan identitas mahasiswa yang login', function () {
    $mahasiswa = mahasiswaDenganAkun(['kelas' => 'Kelas RPL']);

    $this->actingAs($mahasiswa->user)
        ->get(route('absensi.metode'))
        ->assertOk()
        ->assertSee($mahasiswa->user->name)
        ->assertSee('Kelas RPL');
});

test('halaman metode menampilkan status belum absen dan menawarkan absen', function () {
    $mahasiswa = mahasiswaDenganAkun();

    $this->actingAs($mahasiswa->user)
        ->get(route('absensi.metode'))
        ->assertOk()
        ->assertSee('Belum Presen')
        ->assertSee('Presen Sekarang');
});

test('halaman metode menampilkan waktu absen saat sudah presen hari ini', function () {
    $mahasiswa = mahasiswaDenganAkun();

    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'SORE',
        'waktu_mulai' => now()->setTime(15, 0),
        'waktu_selesai' => now()->setTime(17, 0),
        'status' => 'selesai',
    ]);

    Absensi::create([
        'mahasiswa_id' => $mahasiswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now()->setTime(15, 10),
        'status' => 'izin',
        'keterangan' => 'Kurang sehat',
    ]);

    $this->actingAs($mahasiswa->user)
        ->get(route('absensi.metode'))
        ->assertOk()
        ->assertSee('Izin')
        ->assertSee('15:10')
        ->assertSee('SORE')
        ->assertDontSee('Presen Sekarang');
});

/*
| Izin dan sakit dicatat di tabel `absensis` yang sama dengan absensi biasa,
| hanya statusnya yang berbeda. Karena itu pengajuan ini otomatis ikut terlihat
| di dashboard dosen dan rekap admin.
*/

/**
 * Sesi absensi yang sedang berjalan.
 *
 * Dipakai tes yang hanya butuh baris sesi untuk menempel absensi (rekap,
 * riwayat, notifikasi), jadi tidak perlu repot membuat jadwal lebih dulu.
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

/**
 * Jadwal hari ini untuk kelas mahasiswa ini, dengan jam yang mencakup waktu
 * sekarang supaya sesinya langsung berjalan.
 *
 * `geserMenit` menggeser rentangnya untuk membuat sesi kedua pada hari yang
 * sama. Jamnya dipangkas supaya tidak melewati batas hari: kalau tes dijalankan
 * tepat setelah tengah malam, `now()->subHour()` bisa jatuh ke hari kemarin
 * dan sesi justru belum dimulai.
 */
function jadwalBerjalan(Mahasiswa $mahasiswa, array $atributJadwal = [], int $geserMenit = 0): Jadwal
{
    $kelas = Kelas::query()->where('nama_kelas', $mahasiswa->kelas)->first()
        ?? Kelas::factory()->create(['nama_kelas' => $mahasiswa->kelas]);

    $mulai = now()->subHour()->addMinutes($geserMenit);
    $selesai = now()->addHour()->addMinutes($geserMenit);

    if ($mulai->lt(today())) {
        $mulai = today()->copy()->setTime(0, 5);
    }

    if ($selesai->gt(today()->endOfDay())) {
        $selesai = today()->copy()->setTime(23, 55);
    }

    return Jadwal::create([
        'kelas_id' => $kelas->getKey(),
        'dosen_id' => Dosen::factory()->create()->getKey(),
        'mata_kuliah' => 'Informatika',
        'hari' => SesiAbsensiService::namaHari(today()),
        'jam_mulai' => $mulai->format('H:i'),
        'jam_selesai' => $selesai->format('H:i'),
        'tahun_akademik' => now()->year,
        'status' => 'Aktif',
        ...$atributJadwal,
    ]);
}

/**
 * Sesi berjalan untuk kelas mahasiswa, dibuat lewat service yang sama dengan
 * aplikasi supaya kode dan waktunya persis seperti produksi.
 */
function sesiUntuk(Mahasiswa $mahasiswa, array $atributJadwal = [], int $geserMenit = 0): SesiAbsensi
{
    $jadwal = jadwalBerjalan($mahasiswa, $atributJadwal, $geserMenit);

    return app(SesiAbsensiService::class)->pastikanSesiHariIni()
        ->firstWhere('jadwal_id', $jadwal->getKey());
}

/*
| ID Unik dicatat dengan kode milik mahasiswa yang sedang login, bukan kode yang
| dicari dari database. Kalau kodenya dicari, siapa pun bisa mengetik NIM
| temannya dan tercatat hadir untuk orang itu.
*/

test('halaman id unik menampilkan formulir dan kode kartu mahasiswa', function () {
    $mahasiswa = mahasiswaDenganAkun(['nim' => '20240101', 'kelas' => 'Kelas RPL']);
    sesiUntuk($mahasiswa);

    $this->actingAs($mahasiswa->user)
        ->get(route('absensi.id-unik'))
        ->assertOk()
        ->assertSee('Presen Kode NIM')
        ->assertSee('Masukkan Kode')
        ->assertSee('Kartu Presensi')
        ->assertSee('20240101')
        ->assertSee('Kelas RPL')
        ->assertSee($mahasiswa->user->name)
        ->assertSee(route('absensi.id-unik.store'), false);
});

test('halaman id unik menawarkan sesi hari ini sebagai pilihan mata kuliah', function () {
    $mahasiswa = mahasiswaDenganAkun(['nim' => '20240101']);
    sesiUntuk($mahasiswa, ['mata_kuliah' => 'Informatika']);

    $this->actingAs($mahasiswa->user)
        ->get(route('absensi.id-unik'))
        ->assertOk()
        ->assertSee('Mata kuliah')
        ->assertSee('Informatika');
});

test('halaman id unik menampilkan panel tanpa jadwal saat kelas belum punya jadwal', function () {
    $mahasiswa = mahasiswaDenganAkun(['nim' => '20240101']);

    $this->actingAs($mahasiswa->user)
        ->get(route('absensi.id-unik'))
        ->assertOk()
        ->assertSee('Belum ada jadwal kuliah hari ini')
        ->assertDontSee('Masukkan Kode');
});

test('halaman id unik hanya untuk akun mahasiswa', function () {
    $this->get(route('absensi.id-unik'))
        ->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['role' => 'Dosen']))
        ->get(route('absensi.id-unik'))
        ->assertForbidden();
});

test('kode id unik yang benar mencatat kehadiran sebagai hadir', function () {
    $mahasiswa = mahasiswaDenganAkun(['nim' => '20240101']);
    $sesi = sesiUntuk($mahasiswa);

    $this->actingAs($mahasiswa->user)
        ->post(route('absensi.id-unik.store'), ['kode' => '20240101', 'sesi' => $sesi->id])
        ->assertRedirect(route('absensi.id-unik'))
        ->assertSessionHas('sukses');

    $absensi = Absensi::where('mahasiswa_id', $mahasiswa->id)->firstOrFail();

    expect($absensi->status)->toBe('hadir')
        ->and($absensi->sesi_absensi_id)->toBe($sesi->id);
});

test('kode id unik diterima walau ada spasi dan tanda hubung', function () {
    $mahasiswa = mahasiswaDenganAkun(['nim' => '20240101']);
    $sesi = sesiUntuk($mahasiswa);

    $this->actingAs($mahasiswa->user)
        ->post(route('absensi.id-unik.store'), ['kode' => '2024 0101', 'sesi' => $sesi->id])
        ->assertRedirect(route('absensi.id-unik'))
        ->assertSessionHas('sukses');

    expect(Absensi::where('mahasiswa_id', $mahasiswa->id)->count())->toBe(1);
});

test('kode id unik milik mahasiswa lain tidak bisa dipakai', function () {
    $mahasiswa = mahasiswaDenganAkun(['nim' => '20240101']);
    $teman = mahasiswaDenganAkun(['nim' => '20249999']);
    $sesi = sesiUntuk($mahasiswa);

    // Mahasiswa mengetik NIM temannya.
    $this->actingAs($mahasiswa->user)
        ->post(route('absensi.id-unik.store'), ['kode' => $teman->nim, 'sesi' => $sesi->id])
        ->assertRedirect(route('absensi.id-unik'))
        ->assertSessionHas('gagal');

    expect(Absensi::where('mahasiswa_id', $mahasiswa->id)->count())->toBe(0)
        ->and(Absensi::where('mahasiswa_id', $teman->id)->count())->toBe(0);
});

test('kode id unik kosong ditolak', function () {
    $mahasiswa = mahasiswaDenganAkun(['nim' => '20240101']);
    $sesi = sesiUntuk($mahasiswa);

    $this->actingAs($mahasiswa->user)
        ->post(route('absensi.id-unik.store'), ['kode' => '', 'sesi' => $sesi->id])
        ->assertSessionHasErrors('kode');
});

test('id unik tanpa sesi yang dipilih ditolak', function () {
    $mahasiswa = mahasiswaDenganAkun(['nim' => '20240101']);
    sesiUntuk($mahasiswa);

    $this->actingAs($mahasiswa->user)
        ->post(route('absensi.id-unik.store'), ['kode' => '20240101'])
        ->assertSessionHasErrors('sesi');

    expect(Absensi::where('mahasiswa_id', $mahasiswa->id)->count())->toBe(0);
});

test('tidak bisa absen id unik dua kali untuk mata kuliah yang sama', function () {
    $mahasiswa = mahasiswaDenganAkun(['nim' => '20240101']);
    $sesi = sesiUntuk($mahasiswa);

    Absensi::create([
        'mahasiswa_id' => $mahasiswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now()->setTime(7, 30),
        'status' => 'hadir',
    ]);

    $this->actingAs($mahasiswa->user)
        ->post(route('absensi.id-unik.store'), ['kode' => '20240101', 'sesi' => $sesi->id])
        ->assertRedirect(route('absensi.id-unik'))
        ->assertSessionHas('gagal');

    expect(Absensi::where('mahasiswa_id', $mahasiswa->id)->count())->toBe(1);
});

test('mahasiswa bisa absen lagi di hari yang sama untuk mata kuliah berikutnya', function () {
    $mahasiswa = mahasiswaDenganAkun(['nim' => '20240101']);
    $sesiPagi = sesiUntuk($mahasiswa, ['mata_kuliah' => 'Informatika']);

    $this->actingAs($mahasiswa->user)
        ->post(route('absensi.id-unik.store'), ['kode' => '20240101', 'sesi' => $sesiPagi->id])
        ->assertSessionHas('sukses');

    // Sesi kedua dibuat pada kelas dan hari yang sama, tapi rentangnya digeser
    // supaya menjadi sesi mata kuliah terpisah yang juga sedang berjalan.
    $sesiSiang = sesiUntuk($mahasiswa, ['mata_kuliah' => 'Matematika'], 30);

    $this->actingAs($mahasiswa->user)
        ->post(route('absensi.id-unik.store'), ['kode' => '20240101', 'sesi' => $sesiSiang->id])
        ->assertSessionHas('sukses');

    $absensi = Absensi::where('mahasiswa_id', $mahasiswa->id)->get();

    expect($absensi)->toHaveCount(2)
        ->and($absensi->pluck('sesi_absensi_id')->sort()->values()->all())
        ->toBe(collect([$sesiPagi->id, $sesiSiang->id])->sort()->values()->all());
});

test('sesi milik kelas lain tidak bisa dipakai untuk absen', function () {
    // Kelasnya dipastikan berbeda; kalau mengandalkan acakan factory, kedua
    // mahasiswa bisa kebagian kelas yang sama dan tesnya jadi tidak menentu.
    $mahasiswa = mahasiswaDenganAkun(['nim' => '20240101', 'kelas' => 'Kelas A']);
    $teman = mahasiswaDenganAkun(['nim' => '20249999', 'kelas' => 'Kelas B']);
    $sesiKelasLain = sesiUntuk($teman);

    $this->actingAs($mahasiswa->user)
        ->post(route('absensi.id-unik.store'), ['kode' => '20240101', 'sesi' => $sesiKelasLain->id])
        ->assertRedirect(route('absensi.id-unik'))
        ->assertSessionHas('gagal');

    expect(Absensi::where('mahasiswa_id', $mahasiswa->id)->count())->toBe(0);
});

test('id unik ditolak saat sesi sudah berakhir', function () {
    $mahasiswa = mahasiswaDenganAkun(['nim' => '20240101']);
    $sesi = sesiUntuk($mahasiswa);

    // Sesi dimundurkan supaya sudah lewat, apa pun jam tes dijalankan.
    $sesi->forceFill([
        'waktu_mulai' => now()->subHours(2),
        'waktu_selesai' => now()->subHour(),
    ])->save();

    $this->actingAs($mahasiswa->user)
        ->post(route('absensi.id-unik.store'), ['kode' => '20240101', 'sesi' => $sesi->id])
        ->assertRedirect(route('absensi.id-unik'))
        ->assertSessionHas('gagal');

    expect(Absensi::where('mahasiswa_id', $mahasiswa->id)->count())->toBe(0);
});

test('halaman id unik menyembunyikan formulir saat sudah presen', function () {
    $mahasiswa = mahasiswaDenganAkun(['nim' => '20240101']);
    $sesi = sesiUntuk($mahasiswa);

    Absensi::create([
        'mahasiswa_id' => $mahasiswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now()->setTime(7, 30),
        'status' => 'hadir',
    ]);

    $this->actingAs($mahasiswa->user)
        ->get(route('absensi.id-unik'))
        ->assertOk()
        ->assertSee('Kehadiran hari ini sudah tercatat')
        ->assertDontSee('Catat Kehadiran')
        ->assertDontSee(route('absensi.id-unik.store'), false);
});

test('halaman identitas menampilkan data mahasiswa dan form ubah', function () {
    $mahasiswa = mahasiswaDenganAkun([
        'nim' => '20240101',
        'kelas' => 'Kelas H',
        'wali' => 'Budi Santoso',
        'telepon_wali' => '08123456789',
        'jenis_kelamin' => 'L',
    ]);

    $this->actingAs($mahasiswa->user)
        ->get(route('absensi.identitas'))
        ->assertOk()
        ->assertSee('Identitas Mahasiswa')
        ->assertSee('Data yang Bisa Diubah')
        ->assertSee('Data Permanen')
        ->assertSee($mahasiswa->user->name)
        ->assertSee('20240101')
        ->assertSee('Kelas H')
        ->assertSee('Budi Santoso')
        ->assertSee('08123456789')
        ->assertSee($mahasiswa->user->email)
        ->assertSee(route('absensi.identitas.update'), false);
});

test('halaman identitas menampilkan rekap kehadiran bulan ini', function () {
    $mahasiswa = mahasiswaDenganAkun();
    $sesi = sesiBerjalan();

    foreach ([['hadir', 3], ['izin', 1]] as [$status, $jumlah]) {
        for ($i = 0; $i < $jumlah; $i++) {
            Absensi::create([
                'mahasiswa_id' => $mahasiswa->id,
                'sesi_absensi_id' => $sesi->id,
                'waktu_absen' => now()->startOfMonth()->addDays($i)->setTime(7, 30),
                'status' => $status,
            ]);
        }
    }

    $this->actingAs($mahasiswa->user)
        ->get(route('absensi.identitas'))
        ->assertOk()
        // 3 hadir dari 4 catatan = 75%.
        ->assertSee('75', false)
        ->assertSee('4 catatan presensi');
});

test('mahasiswa bisa mengubah nama jenis kelamin dan kontak orang tua', function () {
    $mahasiswa = mahasiswaDenganAkun([
        'nim' => '20240101',
        'kelas' => 'Kelas H',
        'jenis_kelamin' => 'L',
    ]);

    $this->actingAs($mahasiswa->user)
        ->post(route('absensi.identitas.update'), [
            'name' => 'Dina Fitrii',
            'gender' => 'P',
            'parent' => 'Budi Santoso',
            'phone' => '08123456789',
        ])
        ->assertRedirect(route('absensi.identitas'))
        ->assertSessionHas('sukses');

    $mahasiswa->refresh();
    $user = $mahasiswa->user->fresh();

    expect($mahasiswa->jenis_kelamin)->toBe('P')
        ->and($mahasiswa->wali)->toBe('Budi Santoso')
        ->and($mahasiswa->telepon_wali)->toBe('08123456789')
        ->and($user->name)->toBe('Dina Fitrii');
});

/*
| NIM dan kelas menentukan absensi, jadi keduanya harus tetap milik admin.
| Route ini yang membuat halaman identitas hanya mengubah sebagian data.
*/

test('mahasiswa tidak bisa mengubah nim kelas maupun email lewat halaman identitas', function () {
    $mahasiswa = mahasiswaDenganAkun(['nim' => '20240101', 'kelas' => 'Kelas H']);
    $emailAwal = $mahasiswa->user->email;

    // Field yang tidak ada di form tetap dikirim lewat request.
    $this->actingAs($mahasiswa->user)
        ->post(route('absensi.identitas.update'), [
            'name' => 'Dina Fitrii',
            'gender' => 'P',
            'parent' => null,
            'phone' => null,
            'nim' => '99999999',
            'nim' => '99999999',
            'kelas' => 'Kelas A',
            'class' => 'Kelas A',
            'email' => 'penyerang@contoh.id',
        ])
        ->assertRedirect(route('absensi.identitas'));

    $mahasiswa->refresh();

    expect($mahasiswa->nim)->toBe('20240101')
        ->and($mahasiswa->kelas)->toBe('Kelas H')
        ->and($mahasiswa->user->fresh()->email)->toBe($emailAwal);
});

test('perubahan identitas ditolak kalau nama kosong atau jenis kelamin tidak valid', function () {
    $mahasiswa = mahasiswaDenganAkun();

    $this->actingAs($mahasiswa->user)
        ->post(route('absensi.identitas.update'), [
            'name' => '',
            'gender' => 'X',
        ])
        ->assertSessionHasErrors(['name', 'gender']);

    expect($mahasiswa->user->fresh()->name)->not->toBe('');
});

test('halaman identitas hanya untuk akun mahasiswa', function () {
    $this->get(route('absensi.identitas'))
        ->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['role' => 'Dosen']))
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
    $mahasiswa = mahasiswaDenganAkun();
    $sesi = sesiBerjalan();

    foreach ([['hadir', 1], ['izin', 1], ['sakit', 1], ['alpha', 1]] as [$status, $jumlah]) {
        for ($i = 0; $i < $jumlah; $i++) {
            Absensi::create([
                'mahasiswa_id' => $mahasiswa->id,
                'sesi_absensi_id' => $sesi->id,
                'waktu_absen' => now()->startOfMonth()->addDays($i)->setTime(7, 30),
                'status' => $status,
                'keterangan' => $status === 'izin' ? 'Acara keluarga.' : null,
            ]);
        }
    }

    $this->actingAs($mahasiswa->user)
        ->get(route('absensi.notifikasi'))
        ->assertOk()
        ->assertSee('Notifikasi Presensi')
        ->assertSee('7 Hari Terakhir')
        ->assertSee('Rekap Bulan Ini')
        ->assertSee('Riwayat Presensi')
        ->assertSee('4 catatan')
        // Rekap bulan ini: 1 hadir dari 4 catatan = 25%.
        ->assertSee('25', false)
        // Keterangan pada riwayat ikut tampil.
        ->assertSee('Acara keluarga.');
});

test('halaman notifikasi menampilkan alasan tiap status di rekap', function () {
    $mahasiswa = mahasiswaDenganAkun();

    $this->actingAs($mahasiswa->user)
        ->get(route('absensi.notifikasi'))
        ->assertOk()
        ->assertSee('tidak dihitung sebagai alpa')
        ->assertSee('Belum ada catatan sampai batas waktu sesi berakhir.');
});

test('halaman notifikasi menampilkan daftar kosong saat belum ada riwayat', function () {
    $mahasiswa = mahasiswaDenganAkun();

    $this->actingAs($mahasiswa->user)
        ->get(route('absensi.notifikasi'))
        ->assertOk()
        ->assertSee('Belum ada riwayat')
        ->assertSee('Mulai presen');
});

test('halaman notifikasi menampilkan status hari ini beserta waktu dan sesi', function () {
    $mahasiswa = mahasiswaDenganAkun();
    $sesi = sesiBerjalan();

    Absensi::create([
        'mahasiswa_id' => $mahasiswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now()->setTime(7, 30),
        'status' => 'hadir',
    ]);

    $this->actingAs($mahasiswa->user)
        ->get(route('absensi.notifikasi'))
        ->assertOk()
        ->assertSee('Hadir')
        ->assertSee('07:30')
        ->assertSee('PAGI')
        ->assertDontSee('Presen Sekarang');
});

test('halaman notifikasi menampilkan tujuh hari terakhir dengan hari kosong', function () {
    $mahasiswa = mahasiswaDenganAkun();
    $sesi = sesiBerjalan();

    // Hanya dua hari yang punya catatan; lima hari sisanya harus tetap muncul
    // sebagai kosong, bukan hilang dari strip mingguan.
    foreach ([1, 3] as $mundur) {
        Absensi::create([
            'mahasiswa_id' => $mahasiswa->id,
            'sesi_absensi_id' => $sesi->id,
            'waktu_absen' => now()->subDays($mundur)->setTime(7, 30),
            'status' => 'hadir',
        ]);
    }

    $this->actingAs($mahasiswa->user)
        ->get(route('absensi.notifikasi'))
        ->assertOk()
        // Rentang 7 hari: 6 hari lalu sampai hari ini.
        ->assertSee(now()->subDays(6)->translatedFormat('d M'))
        ->assertSee(now()->translatedFormat('d M Y'))
        ->assertSee('2 catatan');
});

test('riwayat notifikasi bisa difilter per mata kuliah', function () {
    $mahasiswa = mahasiswaDenganAkun();

    $aljabar = sesiUntuk($mahasiswa, ['mata_kuliah' => 'Aljabar Linear']);
    $informatika = sesiUntuk($mahasiswa, ['mata_kuliah' => 'Informatika Dasar'], 45);

    Absensi::create([
        'mahasiswa_id' => $mahasiswa->id,
        'sesi_absensi_id' => $aljabar->id,
        'waktu_absen' => now()->subDay()->setTime(7, 30),
        'status' => 'hadir',
        'keterangan' => 'Catatan Aljabar.',
    ]);

    Absensi::create([
        'mahasiswa_id' => $mahasiswa->id,
        'sesi_absensi_id' => $informatika->id,
        'waktu_absen' => now()->subDay()->setTime(9, 0),
        'status' => 'izin',
        'keterangan' => 'Catatan Informatika.',
    ]);

    // Tanpa filter semua catatan tampil, dan formulir filternya ikut ada.
    $this->actingAs($mahasiswa->user)
        ->get(route('absensi.notifikasi'))
        ->assertOk()
        ->assertSee('2 catatan')
        ->assertSee('name="mata_kuliah"', false)
        ->assertSee('Terapkan');

    // Filter satu mata kuliah: hanya catatannya yang tersisa di tabel riwayat.
    $this->actingAs($mahasiswa->user)
        ->get(route('absensi.notifikasi', ['mata_kuliah' => 'Aljabar Linear']))
        ->assertOk()
        ->assertSee('1 catatan')
        ->assertSee('Catatan Aljabar.')
        ->assertDontSee('Catatan Informatika.');
});

test('riwayat notifikasi bisa difilter per status', function () {
    $mahasiswa = mahasiswaDenganAkun();
    $sesi = sesiUntuk($mahasiswa);

    foreach ([['hadir', 'Catatan hadir.'], ['alpha', 'Catatan alpa.']] as $idx => [$status, $keterangan]) {
        Absensi::create([
            'mahasiswa_id' => $mahasiswa->id,
            'sesi_absensi_id' => $sesi->id,
            'waktu_absen' => now()->subDay()->setTime(7, 30)->addMinutes($idx * 90),
            'status' => $status,
            'keterangan' => $keterangan,
        ]);
    }

    $this->actingAs($mahasiswa->user)
        ->get(route('absensi.notifikasi', ['status' => 'alpha']))
        ->assertOk()
        ->assertSee('1 catatan')
        ->assertSee('Catatan alpa.')
        ->assertDontSee('Catatan hadir.');
});

test('filter riwayat notifikasi mengabaikan nilai yang tidak dikenal', function () {
    $mahasiswa = mahasiswaDenganAkun();
    $sesi = sesiUntuk($mahasiswa, ['mata_kuliah' => 'Aljabar Linear']);

    foreach (['Catatan Aljabar.', 'Catatan izin.'] as $idx => $keterangan) {
        Absensi::create([
            'mahasiswa_id' => $mahasiswa->id,
            'sesi_absensi_id' => $sesi->id,
            'waktu_absen' => now()->subDay()->setTime(7, 30)->addMinutes($idx * 90),
            'status' => $idx === 0 ? 'hadir' : 'izin',
            'keterangan' => $keterangan,
        ]);
    }

    // Pilihan di luar daftar diabaikan, bukan membuat halaman kosong.
    $this->actingAs($mahasiswa->user)
        ->get(route('absensi.notifikasi', ['mata_kuliah' => 'Tidak Pernah Diikuti', 'status' => 'libur']))
        ->assertOk()
        ->assertSee('2 catatan')
        ->assertDontSee('difilter')
        ->assertSee('Catatan Aljabar.')
        ->assertSee('Catatan izin.');

    // Filter valid tapi tidak ada catatannya: pesannya menyesuaikan.
    $this->actingAs($mahasiswa->user)
        ->get(route('absensi.notifikasi', ['status' => 'sakit']))
        ->assertOk()
        ->assertSee('0 catatan')
        ->assertSee('Tidak ada catatan yang cocok')
        ->assertDontSee('Mulai presen');
});

test('riwayat notifikasi bisa difilter untuk sesi tanpa jadwal kuliah', function () {
    $mahasiswa = mahasiswaDenganAkun();

    $sesiLama = sesiBerjalan(); // sesi lama tanpa jadwal_id
    $sesiBaru = sesiUntuk($mahasiswa, ['mata_kuliah' => 'Aljabar Linear']);

    Absensi::create([
        'mahasiswa_id' => $mahasiswa->id,
        'sesi_absensi_id' => $sesiLama->id,
        'waktu_absen' => now()->subDay()->setTime(7, 30),
        'status' => 'hadir',
        'keterangan' => 'Catatan tanpa jadwal.',
    ]);

    Absensi::create([
        'mahasiswa_id' => $mahasiswa->id,
        'sesi_absensi_id' => $sesiBaru->id,
        'waktu_absen' => now()->subDay()->setTime(9, 0),
        'status' => 'hadir',
        'keterangan' => 'Catatan berjadwal.',
    ]);

    $this->actingAs($mahasiswa->user)
        ->get(route('absensi.notifikasi', ['mata_kuliah' => SesiAbsensi::TANPA_JADWAL]))
        ->assertOk()
        ->assertSee('1 catatan')
        ->assertSee('Catatan tanpa jadwal.')
        ->assertDontSee('Catatan berjadwal.');
});

test('halaman notifikasi hanya untuk akun mahasiswa', function () {
    $this->get(route('absensi.notifikasi'))
        ->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['role' => 'Dosen']))
        ->get(route('absensi.notifikasi'))
        ->assertForbidden();
});

test('halaman izin sakit menampilkan formulir dan identitas mahasiswa', function () {
    $mahasiswa = mahasiswaDenganAkun(['kelas' => 'Kelas RPL']);
    sesiUntuk($mahasiswa, ['mata_kuliah' => 'Informatika']);

    $this->actingAs($mahasiswa->user)
        ->get(route('absensi.izin-sakit'))
        ->assertOk()
        ->assertSee('Izin / Sakit')
        ->assertSee('Formulir Pengajuan')
        ->assertSee($mahasiswa->user->name)
        ->assertSee('Kelas RPL')
        // Pengajuan melekat pada sesi, jadi jadwal hari ini ikut ditawarkan.
        ->assertSee('Informatika')
        ->assertSee(route('absensi.izin-sakit.store'), false)
        // Dua jenis pengajuan harus selectable.
        ->assertSee('value="izin"', false)
        ->assertSee('value="sakit"', false);
});

test('halaman izin sakit menampilkan panel tanpa jadwal saat kelas belum punya jadwal', function () {
    $mahasiswa = mahasiswaDenganAkun();

    $this->actingAs($mahasiswa->user)
        ->get(route('absensi.izin-sakit'))
        ->assertOk()
        ->assertSee('Belum ada jadwal kuliah hari ini')
        ->assertDontSee('Formulir Pengajuan');
});

test('halaman izin sakit hanya untuk akun mahasiswa', function () {
    $this->get(route('absensi.izin-sakit'))
        ->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['role' => 'Dosen']))
        ->get(route('absensi.izin-sakit'))
        ->assertForbidden();
});

test('pengajuan izin tersimpan sebagai absensi berstatus izin', function () {
    $mahasiswa = mahasiswaDenganAkun();
    $sesi = sesiUntuk($mahasiswa);

    $this->actingAs($mahasiswa->user)
        ->post(route('absensi.izin-sakit.store'), [
            'jenis' => 'izin',
            'alasan' => 'Ada acara nikah vina di luar kota hari ini.',
            'sesi' => $sesi->id,
        ])
        ->assertRedirect(route('absensi.izin-sakit'))
        ->assertSessionHas('sukses');

    $absensi = Absensi::where('mahasiswa_id', $mahasiswa->id)->firstOrFail();

    expect($absensi->status)->toBe('izin')
        ->and($absensi->sesi_absensi_id)->toBe($sesi->id)
        ->and($absensi->keterangan)->toContain('nikah vina');
});

test('pengajuan sakit tersimpan sebagai absensi berstatus sakit', function () {
    $mahasiswa = mahasiswaDenganAkun();
    $sesi = sesiUntuk($mahasiswa);

    $this->actingAs($mahasiswa->user)
        ->post(route('absensi.izin-sakit.store'), [
            'jenis' => 'sakit',
            'alasan' => 'Demam sejak semalam dan sudah minum obat.',
            'sesi' => $sesi->id,
        ])
        ->assertRedirect(route('absensi.izin-sakit'));

    expect(Absensi::where('mahasiswa_id', $mahasiswa->id)->value('status'))->toBe('sakit');
});

test('pengajuan tanpa alasan yang jelas ditolak', function () {
    $mahasiswa = mahasiswaDenganAkun();
    $sesi = sesiUntuk($mahasiswa);

    $this->actingAs($mahasiswa->user)
        ->post(route('absensi.izin-sakit.store'), [
            'jenis' => 'izin',
            'alasan' => 'sakit',
            'sesi' => $sesi->id,
        ])
        ->assertSessionHasErrors('alasan');

    expect(Absensi::where('mahasiswa_id', $mahasiswa->id)->count())->toBe(0);
});

test('jenis pengajuan di luar izin dan sakit ditolak', function () {
    $mahasiswa = mahasiswaDenganAkun();
    $sesi = sesiUntuk($mahasiswa);

    $this->actingAs($mahasiswa->user)
        ->post(route('absensi.izin-sakit.store'), [
            'jenis' => 'hadir',
            'alasan' => 'Mau absen biasa saja hari ini.',
            'sesi' => $sesi->id,
        ])
        ->assertSessionHasErrors('jenis');

    expect(Absensi::where('mahasiswa_id', $mahasiswa->id)->count())->toBe(0);
});

test('pengajuan untuk sesi yang sudah berakhir ditolak', function () {
    $mahasiswa = mahasiswaDenganAkun();
    $sesi = sesiUntuk($mahasiswa);

    $sesi->forceFill([
        'waktu_mulai' => now()->subHours(2),
        'waktu_selesai' => now()->subHour(),
    ])->save();

    $this->actingAs($mahasiswa->user)
        ->post(route('absensi.izin-sakit.store'), [
            'jenis' => 'izin',
            'alasan' => 'Izin untuk jam pelajaran yang sudah lewat.',
            'sesi' => $sesi->id,
        ])
        ->assertRedirect(route('absensi.izin-sakit'))
        ->assertSessionHas('gagal');

    expect(Absensi::where('mahasiswa_id', $mahasiswa->id)->count())->toBe(0);
});

test('pengajuan ditolak untuk sesi milik kelas lain', function () {
    // Kelasnya dipastikan berbeda supaya tes tidak flaky seperti acakan factory.
    $mahasiswa = mahasiswaDenganAkun(['kelas' => 'Kelas A']);
    $teman = mahasiswaDenganAkun(['kelas' => 'Kelas B']);
    $sesiKelasLain = sesiUntuk($teman);

    $this->actingAs($mahasiswa->user)
        ->post(route('absensi.izin-sakit.store'), [
            'jenis' => 'izin',
            'alasan' => 'Mau izin di sesi kelas orang lain.',
            'sesi' => $sesiKelasLain->id,
        ])
        ->assertRedirect(route('absensi.izin-sakit'))
        ->assertSessionHas('gagal');

    expect(Absensi::where('mahasiswa_id', $mahasiswa->id)->count())->toBe(0);
});

test('tidak bisa mengirim pengajuan kedua untuk sesi yang sama', function () {
    $mahasiswa = mahasiswaDenganAkun();
    $sesi = sesiUntuk($mahasiswa);

    // Kehadiran sudah tercatat di sesi itu.
    Absensi::create([
        'mahasiswa_id' => $mahasiswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now()->setTime(7, 30),
        'status' => 'hadir',
    ]);

    $this->actingAs($mahasiswa->user)
        ->post(route('absensi.izin-sakit.store'), [
            'jenis' => 'izin',
            'alasan' => 'Mau izin padahal sudah presen di sesi ini.',
            'sesi' => $sesi->id,
        ])
        ->assertRedirect(route('absensi.izin-sakit'))
        ->assertSessionHas('gagal');

    expect(Absensi::where('mahasiswa_id', $mahasiswa->id)->count())->toBe(1);
});

test('pengajuan tanpa sesi yang dipilih ditolak', function () {
    $mahasiswa = mahasiswaDenganAkun();
    sesiUntuk($mahasiswa);

    $this->actingAs($mahasiswa->user)
        ->post(route('absensi.izin-sakit.store'), [
            'jenis' => 'izin',
            'alasan' => 'Izin tapi tidak memilih mata kuliahnya.',
        ])
        ->assertSessionHasErrors('sesi');

    expect(Absensi::where('mahasiswa_id', $mahasiswa->id)->count())->toBe(0);
});

test('halaman izin sakit menampilkan riwayat pengajuan', function () {
    $mahasiswa = mahasiswaDenganAkun();
    $sesi = sesiBerjalan();

    Absensi::create([
        'mahasiswa_id' => $mahasiswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now()->subDays(3)->setTime(7, 30),
        'status' => 'sakit',
        'keterangan' => 'Demam dua hari lalu.',
    ]);

    $this->actingAs($mahasiswa->user)
        ->get(route('absensi.izin-sakit'))
        ->assertOk()
        ->assertSee('Pengajuan Terakhir')
        ->assertSee('Demam dua hari lalu.');
});

test('halaman izin sakit menampilkan pesan saat kehadiran sudah tercatat', function () {
    $mahasiswa = mahasiswaDenganAkun();
    $sesi = sesiUntuk($mahasiswa);

    // Satu-satunya jadwal hari ini sudah punya catatan, jadi tidak ada sesi
    // yang bisa diajukan lagi.
    Absensi::create([
        'mahasiswa_id' => $mahasiswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now()->setTime(7, 30),
        'status' => 'hadir',
    ]);

    $this->actingAs($mahasiswa->user)
        ->get(route('absensi.izin-sakit'))
        ->assertOk()
        ->assertSee('Kehadiran hari ini sudah tercatat')
        ->assertDontSee('Formulir Pengajuan');
});

test('halaman metode hanya bisa dibuka oleh akun mahasiswa', function () {
    $this->get(route('absensi.metode'))
        ->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['role' => 'Dosen']))
        ->get(route('absensi.metode'))
        ->assertForbidden();

    $this->actingAs(User::factory()->create(['role' => 'Admin']))
        ->get(route('absensi.metode'))
        ->assertForbidden();
});

/*
| Scan QR Sesi: mahasiswa memindai QR yang ditampilkan dosen atau admin di
| depan kelas (halaman Pindai Barcode). Isinya kode sesi, jadi begitu terbaca,
| halaman mengirimkannya sebagai JSON dan kehadiran dicatat tanpa form.
*/

test('halaman scan qr hanya untuk akun mahasiswa', function () {
    $this->get(route('absensi.scan-qr'))
        ->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create(['role' => 'Dosen']))
        ->get(route('absensi.scan-qr'))
        ->assertForbidden();
});

test('halaman scan qr mengirim alamat simpan untuk skrip kameranya', function () {
    $mahasiswa = mahasiswaDenganAkun();

    $this->actingAs($mahasiswa->user)
        ->get(route('absensi.scan-qr'))
        ->assertOk()
        // Alamatnya dikirim sebagai JSON ke dalam skrip kameranya.
        ->assertSee('RUTE_SIMPAN', false)
        ->assertSee('meta name="csrf-token"', false);
});

test('scan qr sesi yang sedang berjalan mencatat kehadiran sebagai hadir', function () {
    $mahasiswa = mahasiswaDenganAkun(['nim' => '20240101']);
    $sesi = sesiUntuk($mahasiswa);

    $this->actingAs($mahasiswa->user)
        ->postJson(route('absensi.scan-qr.store'), ['kode' => $sesi->kode_sesi])
        ->assertOk()
        ->assertJson(['sukses' => true]);

    $absensi = Absensi::where('mahasiswa_id', $mahasiswa->id)->firstOrFail();

    expect($absensi->status)->toBe('hadir')
        ->and($absensi->sesi_absensi_id)->toBe($sesi->id);
});

test('scan ulang pada sesi yang sama ditolak dan tidak menggandakan catatan', function () {
    $mahasiswa = mahasiswaDenganAkun();
    $sesi = sesiUntuk($mahasiswa);

    $this->actingAs($mahasiswa->user)
        ->postJson(route('absensi.scan-qr.store'), ['kode' => $sesi->kode_sesi])
        ->assertOk();

    $this->actingAs($mahasiswa->user)
        ->postJson(route('absensi.scan-qr.store'), ['kode' => $sesi->kode_sesi])
        ->assertStatus(422)
        ->assertJson(['sukses' => false]);

    expect(Absensi::where('mahasiswa_id', $mahasiswa->id)->count())->toBe(1);
});

test('scan qr milik kelas lain ditolak', function () {
    // Kelasnya dipastikan berbeda supaya tes tidak flaky seperti acakan factory.
    $mahasiswa = mahasiswaDenganAkun(['kelas' => 'Kelas A']);
    $teman = mahasiswaDenganAkun(['kelas' => 'Kelas B']);
    $sesiKelasLain = sesiUntuk($teman);

    $this->actingAs($mahasiswa->user)
        ->postJson(route('absensi.scan-qr.store'), ['kode' => $sesiKelasLain->kode_sesi])
        ->assertStatus(422)
        ->assertJson(['sukses' => false]);

    expect(Absensi::where('mahasiswa_id', $mahasiswa->id)->count())->toBe(0);
});

test('scan qr ditolak saat sesi sudah berakhir', function () {
    $mahasiswa = mahasiswaDenganAkun();
    $sesi = sesiUntuk($mahasiswa);

    // Sesi dimundurkan supaya sudah lewat, apa pun jam tes dijalankan.
    $sesi->forceFill([
        'waktu_mulai' => now()->subHours(2),
        'waktu_selesai' => now()->subHour(),
    ])->save();

    $this->actingAs($mahasiswa->user)
        ->postJson(route('absensi.scan-qr.store'), ['kode' => $sesi->kode_sesi])
        ->assertStatus(422)
        ->assertJson(['sukses' => false]);

    expect(Absensi::where('mahasiswa_id', $mahasiswa->id)->count())->toBe(0);
});

test('kode qr yang tidak dikenal ditolak', function () {
    $mahasiswa = mahasiswaDenganAkun();

    $this->actingAs($mahasiswa->user)
        ->postJson(route('absensi.scan-qr.store'), ['kode' => 'JW999-2020-01-01'])
        ->assertStatus(422)
        ->assertJson(['sukses' => false]);
});

test('mahasiswa nonaktif tidak bisa presen lewat scan qr', function () {
    $mahasiswa = mahasiswaDenganAkun(['status' => 'Nonaktif']);
    $sesi = sesiUntuk($mahasiswa);

    $this->actingAs($mahasiswa->user)
        ->postJson(route('absensi.scan-qr.store'), ['kode' => $sesi->kode_sesi])
        ->assertStatus(403)
        ->assertJson(['sukses' => false]);

    expect(Absensi::where('mahasiswa_id', $mahasiswa->id)->count())->toBe(0);
});

test('mahasiswa nonaktif tidak bisa presen lewat kode nim', function () {
    $mahasiswa = mahasiswaDenganAkun(['nim' => '20240101', 'status' => 'Nonaktif']);
    $sesi = sesiUntuk($mahasiswa);

    $this->actingAs($mahasiswa->user)
        ->post(route('absensi.id-unik.store'), ['kode' => '20240101', 'sesi' => $sesi->id])
        ->assertRedirect(route('absensi.id-unik'))
        ->assertSessionHas('gagal');

    expect(Absensi::where('mahasiswa_id', $mahasiswa->id)->count())->toBe(0);
});

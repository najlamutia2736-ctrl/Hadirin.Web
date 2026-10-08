<?php

use App\Models\Absensi;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\SesiAbsensi;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Alur data antara dashboard admin (CMS) dan dashboard guru.
 *
 * Fokusnya: apa pun yang disimpan dari dashboard CMS harus muncul di dashboard
 * guru, dan dashboard guru hanya boleh menampilkan kelas milik guru yang
 * sedang login.
 */
uses(RefreshDatabase::class);

/**
 * Guru lengkap dengan akun penggunanya.
 */
function guruDenganAkun(string $nama = 'Guru Uji'): Guru
{
    $user = User::factory()->create([
        'name' => $nama,
        'role' => 'Guru',
        'status' => 'Aktif',
    ]);

    return Guru::factory()->create(['user_id' => $user->id]);
}

/**
 * Siswa yang masuk ke sebuah kelas.
 */
function siswaDiKelas(Kelas $kelas, string $nama = 'Siswa Uji'): Siswa
{
    $user = User::factory()->create([
        'name' => $nama,
        'role' => 'Siswa',
        'status' => 'Aktif',
    ]);

    return Siswa::factory()->create([
        'user_id' => $user->id,
        'kelas' => $kelas->nama_kelas,
    ]);
}

test('kelas yang disimpan dari dashboard cms menautkan guru pengampu', function () {
    loginAdmin();
    $guru = guruDenganAkun();

    $this->post(route('cms.classes.store'), [
        'name' => 'XII-C',
        'level' => 'XII',
        'tahun_ajaran' => now()->year,
        'teachers' => [$guru->id],
    ])->assertRedirect(route('cms.classes'));

    $kelas = Kelas::query()->where('nama_kelas', 'XII-C')->firstOrFail();

    expect($kelas->guru()->pluck('gurus.id')->all())->toBe([$guru->id]);
    expect($guru->kelasDiampu()->pluck('kelas.id')->all())->toBe([$kelas->id]);
});

test('form ubah kelas mengganti daftar guru pengampu', function () {
    loginAdmin();
    $guruAwal = guruDenganAkun('Guru Awal');
    $guruBaru = guruDenganAkun('Guru Baru');
    $kelas = Kelas::factory()->create();
    $kelas->guru()->sync([$guruAwal->id]);

    $this->put(route('cms.classes.update', $kelas), [
        'name' => $kelas->nama_kelas,
        'level' => $kelas->tingkat,
        'status' => 'Aktif',
        'teachers' => [$guruBaru->id],
    ])->assertRedirect(route('cms.classes'));

    expect($kelas->fresh()->guru()->pluck('gurus.id')->all())->toBe([$guruBaru->id]);
});

test('guru pengampu yang tidak lagi dipilih dilepas dari kelas', function () {
    loginAdmin();
    $guru = guruDenganAkun();
    $kelas = Kelas::factory()->create();
    $kelas->guru()->sync([$guru->id]);

    $this->put(route('cms.classes.update', $kelas), [
        'name' => $kelas->nama_kelas,
        'level' => $kelas->tingkat,
        'status' => 'Aktif',
        'teachers' => [],
    ])->assertRedirect(route('cms.classes'));

    expect($kelas->fresh()->guru()->count())->toBe(0);
});

test('dashboard guru hanya menampilkan kelas yang diampu guru tersebut', function () {
    $guru = guruDenganAkun();

    $kelasMilik = Kelas::factory()->create(['nama_kelas' => 'XII-MILIK', 'status' => 'Aktif']);
    $kelasLain = Kelas::factory()->create(['nama_kelas' => 'XII-LAIN', 'status' => 'Aktif']);

    $kelasMilik->guru()->sync([$guru->id]);

    $this->actingAs($guru->user)
        ->get(route('guru.dashboard'))
        ->assertOk()
        ->assertSee('XII-MILIK')
        ->assertDontSee('XII-LAIN');
});

test('siswa yang ditambah dari dashboard cms muncul di dashboard guru', function () {
    $guru = guruDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelas->guru()->sync([$guru->id]);

    $siswa = siswaDiKelas($kelas, 'Siswa Baru Dari CMS');

    $this->actingAs($guru->user)
        ->get(route('guru.dashboard'))
        ->assertOk()
        ->assertSee('Siswa Baru Dari CMS');
});

test('kelas arsip dan kelas nonaktif tidak tampil di dashboard guru', function () {
    $guru = guruDenganAkun();

    $aktif = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $arsip = Kelas::factory()->create(['nama_kelas' => 'X-ARSIP', 'status' => 'Arsip']);

    $aktif->guru()->sync([$guru->id]);
    $arsip->guru()->sync([$guru->id]);

    $this->actingAs($guru->user)
        ->get(route('guru.dashboard'))
        ->assertOk()
        ->assertSee('X-A')
        ->assertDontSee('X-ARSIP');
});

test('absensi hari ini di database dipakai dashboard guru, bukan data contoh', function () {
    $guru = guruDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelas->guru()->sync([$guru->id]);

    $hadir = siswaDiKelas($kelas, 'Siswa Hadir');
    $izin = siswaDiKelas($kelas, 'Siswa Izin');

    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'SESI-'.now()->format('Ymd').'-01',
        'waktu_mulai' => now()->subHour(),
        'waktu_selesai' => now()->addHour(),
        'status' => 'aktif',
    ]);

    Absensi::create([
        'siswa_id' => $hadir->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now(),
        'status' => 'hadir',
    ]);

    Absensi::create([
        'siswa_id' => $izin->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now(),
        'status' => 'izin',
    ]);

    $this->actingAs($guru->user)
        ->get(route('guru.dashboard'))
        ->assertOk()
        ->assertSee('Siswa Hadir')
        ->assertSee('Siswa Izin');
});

test('absensi bukan hari ini tidak dihitung sebagai kehadiran hari ini', function () {
    $guru = guruDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelas->guru()->sync([$guru->id]);

    $siswa = siswaDiKelas($kelas, 'Siswa Absen Kemarin');

    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'SESI-LAMPAU',
        'waktu_mulai' => now()->subDays(2),
        'waktu_selesai' => now()->subDays(2)->addHour(),
        'status' => 'selesai',
    ]);

    Absensi::create([
        'siswa_id' => $siswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now()->subDays(2),
        'status' => 'hadir',
    ]);

    // Nama siswa tetap tampil, tapi status kehadiran hari ini harus null
    // (belum absen), bukan "hadir" dari absensi dua hari lalu.
    $payload = $this->actingAs($guru->user)
        ->getJson(route('guru.dashboard'))
        ->assertOk()
        ->json();

    $this->assertSame('X-A', $payload['kelas'][0]['nama']);
    $this->assertSame('Aktif', $payload['kelas'][0]['siswa'][0]['status']);
    $this->assertNull($payload['kelas'][0]['siswa'][0]['absensi']);
});

test('dashboard guru mengirim data kelas dan siswa ke browser sebagai json', function () {
    $guru = guruDenganAkun('Budi Santoso, S.Pd');
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelas->guru()->sync([$guru->id]);
    siswaDiKelas($kelas, 'Ani');

    $payload = $this->actingAs($guru->user)
        ->getJson(route('guru.dashboard'))
        ->assertOk()
        ->json();

    expect($payload['teraut'])->toBeTrue()
        ->and($payload['guru']['nama'])->toBe('Budi Santoso, S.Pd')
        ->and($payload['guru']['kelas'])->toBe('X-A')
        ->and($payload['kelas'])->toHaveCount(1)
        ->and($payload['kelas'][0]['nama'])->toBe('X-A')
        ->and($payload['kelas'][0]['total'])->toBe(1)
        ->and($payload['kelas'][0]['siswa'][0]['nama'])->toBe('Ani');
});

test('tanpa login dashboard guru ditolak, bukan menampilkan pratinjau kelas', function () {
    // Dulu halaman ini sengaja membuka "mode pratinjau" untuk pengunjung yang
    // belum login. Sekarang area guru dilindungi middleware `auth`, jadi data
    // kelas tidak boleh bocor sebelum pengguna masuk.
    Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);

    $this->getJson(route('guru.dashboard'))
        ->assertUnauthorized();

    $this->get(route('guru.dashboard'))
        ->assertRedirect(route('login'));
});

test('sidebar dan header memakai nama user yang sedang login', function () {
    $guru = guruDenganAkun('Guru Login');

    $this->actingAs($guru->user)
        ->get(route('guru.dashboard'))
        ->assertOk()
        ->assertSee('Guru Login')
        ->assertSee($guru->user->email);
});

test('login guru diarahkan ke dashboard guru', function () {
    $guru = guruDenganAkun();
    $user = $guru->user;

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('guru.dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('login admin diarahkan ke dashboard cms', function () {
    $user = User::factory()->create(['role' => 'Admin', 'status' => 'Aktif']);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('cms.dashboard'));
});

test('login dengan password salah ditolak tanpa membocorkan email terdaftar', function () {
    $user = User::factory()->create(['role' => 'Guru', 'status' => 'Aktif']);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password-salah',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('akun nonaktif tidak bisa login', function () {
    $user = User::factory()->create(['role' => 'Guru', 'status' => 'Nonaktif']);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('logout mengakhiri sesi', function () {
    $guru = guruDenganAkun();

    $this->actingAs($guru->user)
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

/*
| `POST /login` memakai middleware `guest`. Kalau tidak diberi arah, middleware
| itu mencari route bernama `dashboard`, lalu `home`. Route dashboard di sini
| bernama `cms.dashboard`, sehingga orang yang sudah login lalu submit form login
| lagi akan mendarat di halaman awal, bukan beranda.
*/

test('login saat sudah login diarahkan ke beranda, bukan halaman awal', function () {
    $user = User::factory()->create(['role' => 'Siswa', 'status' => 'Aktif']);

    $this->actingAs($user)
        ->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ])
        ->assertRedirect(route('beranda'));
});

test('halaman beranda punya form logout yang benar-benar mengirim post', function () {
    // Halaman `home` sengaja tidak ikut di sini: ia adalah pintu masuk dan
    // mengakhiri sesi, jadi tidak mungkin menampilkan form logout.
    $user = User::factory()->create(['role' => 'Siswa', 'status' => 'Aktif']);

    $this->actingAs($user)
        ->get(route('beranda'))
        ->assertOk()
        ->assertSee('<form method="POST" action="'.route('logout').'"', false);
});

/*
| Halaman depan ada di `/`. `/beranda` lama hanya pengalihan ke sana, jadi
| halaman yang sama berlaku untuk pengunjung umum maupun pengguna yang sudah
| masuk, dan sesi tidak lagi diakhiri oleh halaman mana pun.
*/

test('halaman depan beralamat di akar situs', function () {
    expect(route('beranda'))->toBe(url('/'));
});

test('alamat lama beranda hanya mengalihkan ke halaman depan', function () {
    $this->get('/beranda')
        ->assertRedirect(route('beranda'));
});

test('halaman beranda tidak mengakhiri sesi', function () {
    $user = User::factory()->create(['role' => 'Siswa', 'status' => 'Aktif']);

    $this->actingAs($user)->get(route('beranda'))->assertOk();

    $this->assertAuthenticatedAs($user);
});

test('halaman depan menampilkan identitas dan link sesuai peran yang masuk', function () {
    // Beranda sekarang satu halaman untuk semua orang, jadi yang menentukan
    // adalah navbar dan tombol hero: guru melihat dashboardnya, akun lain
    // tidak boleh melihat link yang bukan haknya.
    $guru = guruDenganAkun('Martha Arinda S.Pd');

    $this->actingAs($guru->user)
        ->get(route('beranda'))
        ->assertOk()
        ->assertSee($guru->user->name)
        ->assertSee('Logout')
        // Link navbar ke dashboard gurunya sendiri boleh tampil.
        ->assertSee('>Dashboard Guru<', false)
        ->assertDontSee('>Dashboard Admin<', false);

    $admin = User::factory()->create(['name' => 'Budi Santoso', 'role' => 'Admin', 'status' => 'Aktif']);

    $this->actingAs($admin)
        ->get(route('beranda'))
        ->assertOk()
        ->assertSee('>Dashboard Admin<', false)
        ->assertDontSee('>Dashboard Guru<', false);
});

test('halaman depan untuk tamu tetap menampilkan pintu masuk', function () {
    // Hero memakai navbar yang sama persis, jadi nama akun dan tombol Logout
    // tidak boleh bocor ke pengunjung yang belum masuk.
    $this->get(route('beranda'))
        ->assertOk()
        ->assertSee(route('login'), false)
        ->assertSee('GET STARTED')
        ->assertDontSee('Logout')
        ->assertDontSee('fa-user-circle', false);
});

test('halaman depan tidak menghapus data yang sudah disimpan', function () {
    // Membuka beranda hanya membaca tampilan. Baris yang sudah ada di database
    // harus tetap utuh.
    $guru = guruDenganAkun();
    $kelas = Kelas::factory()->create(['status' => 'Aktif']);
    $kelas->guru()->sync([$guru->id]);
    $siswa = siswaDiKelas($kelas, 'Siswa Tetap Ada');

    $jumlahSiswa = Siswa::count();
    $jumlahGuru = Guru::count();
    $jumlahKelas = Kelas::count();

    $this->actingAs($guru->user)->get(route('beranda'))->assertOk();

    expect(Siswa::count())->toBe($jumlahSiswa)
        ->and(Guru::count())->toBe($jumlahGuru)
        ->and(Kelas::count())->toBe($jumlahKelas)
        ->and(Siswa::query()->find($siswa->id))->not->toBeNull()
        ->and($guru->fresh())->not->toBeNull()
        ->and($kelas->fresh()->guru()->pluck('gurus.id')->all())->toBe([$guru->id]);
});

test('waktu login terakhir tersimpan di database', function () {
    $guru = guruDenganAkun();
    $user = $guru->user;

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    expect($user->fresh()->last_login_at)->not->toBeNull();
});

/*
| Validasi `unique` saat mengubah data harus mengabaikan baris itu sendiri.
| Kalau tidak, menyimpan ulang nilai lama (mis. nama kelas tidak diubah)
| selalu ditolak sebagai duplikat.
*/

test('kelas boleh disimpan ulang tanpa mengubah nama kelasnya', function () {
    loginAdmin();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X-A', 'tingkat' => 'X']);

    $this->put(route('cms.classes.update', $kelas), [
        'name' => 'X-A',
        'level' => 'X',
        'room' => 'R. 999',
        'status' => 'Aktif',
    ])->assertSessionHasNoErrors();

    expect($kelas->fresh()->ruang)->toBe('R. 999');
});

test('siswa boleh disimpan ulang tanpa mengubah nis nya', function () {
    loginAdmin();
    $siswa = Siswa::factory()->create(['nisn' => '12345678']);
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X-A']);

    $this->put(route('cms.student.update', $siswa), [
        'name' => 'Nama Baru',
        'nis' => '12345678',
        'class' => $kelas->nama_kelas,
        'gender' => 'L',
        'status' => 'Aktif',
    ])->assertSessionHasNoErrors();

    expect($siswa->fresh()->user->name)->toBe('Nama Baru');
});

test('guru boleh disimpan ulang tanpa mengubah nip nya', function () {
    loginAdmin();
    $guru = Guru::factory()->create(['nip' => '198001010001']);

    $this->put(route('cms.teachers.update', $guru), [
        'name' => 'Nama Baru',
        'nip' => '198001010001',
        'subject' => $guru->mata_pelajaran_id,
        'status' => 'Aktif',
    ])->assertSessionHasNoErrors();

    expect($guru->fresh()->user->name)->toBe('Nama Baru');
});

test('pengguna boleh disimpan ulang tanpa mengubah email nya', function () {
    loginAdmin();
    $user = User::factory()->create(['email' => 'guru@sekolah.sch.id']);

    $this->put(route('cms.users.update', $user), [
        'name' => 'Nama Baru',
        'email' => 'guru@sekolah.sch.id',
        'role' => 'Guru',
        'status' => 'Aktif',
    ])->assertSessionHasNoErrors();

    expect($user->fresh()->name)->toBe('Nama Baru');
});

/*
| Halaman Kelola Data Kelas. Guru boleh mengelola siswa di kelas yang diampu,
| dan tidak boleh menyentuh kelas milik guru lain.
*/

/*
| Laporan Bulanan. Semuanya dihitung dari tabel `absensis` yang sama dengan
| rekap admin, lalu dibatasi ke kelas milik guru yang sedang login.
*/

test('laporan bulanan menghitung kehadiran dari tabel absensi', function () {
    $guru = guruDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelas->guru()->sync([$guru->id]);

    $hadir = siswaDiKelas($kelas, 'Siswa Hadir');
    $izin = siswaDiKelas($kelas, 'Siswa Izin');
    $alpa = siswaDiKelas($kelas, 'Siswa Alpa');

    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'SESI-LAPORAN',
        'waktu_mulai' => now()->startOfMonth(),
        'waktu_selesai' => now()->endOfMonth(),
        'status' => 'aktif',
    ]);

    foreach ([[$hadir, 'hadir'], [$izin, 'izin'], [$alpa, 'alpha']] as [$siswa, $status]) {
        Absensi::create([
            'siswa_id' => $siswa->id,
            'sesi_absensi_id' => $sesi->id,
            'waktu_absen' => now(),
            'status' => $status,
        ]);
    }

    $this->actingAs($guru->user)
        ->get(route('guru.laporan'))
        ->assertOk()
        ->assertSee('Laporan Bulanan')
        ->assertSee('Siswa Hadir')
        ->assertSee('Siswa Alpa');
});

test('laporan bulanan memakai json saat diminta', function () {
    $guru = guruDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelas->guru()->sync([$guru->id]);

    $siswa = siswaDiKelas($kelas, 'Siswa Json');

    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'SESI-JSON',
        'waktu_mulai' => now(),
        'waktu_selesai' => now()->addHour(),
        'status' => 'aktif',
    ]);

    Absensi::create([
        'siswa_id' => $siswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now(),
        'status' => 'hadir',
    ]);

    $payload = $this->actingAs($guru->user)
        ->getJson(route('guru.laporan'))
        ->assertOk()
        ->json();

    expect($payload['total']['hadir'])->toBe(1)
        ->and($payload['total']['persentase'])->toBe(100)
        ->and($payload['perKelas'][0]['kelas'])->toBe('X-A')
        ->and($payload['perSiswa'][0]['nama'])->toBe('Siswa Json')
        ->and($payload['perSiswa'][0]['hari'])->toBe(1);
});

test('laporan bulanan hanya menghitung kelas yang diampu guru', function () {
    $guru = guruDenganAkun();
    $kelasMilik = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelasLain = Kelas::factory()->create(['nama_kelas' => 'X-B', 'status' => 'Aktif']);
    $kelasMilik->guru()->sync([$guru->id]);

    siswaDiKelas($kelasMilik, 'Siswa Milik Saya');
    siswaDiKelas($kelasLain, 'Siswa Milik Orang Lain');

    $this->actingAs($guru->user)
        ->get(route('guru.laporan'))
        ->assertOk()
        ->assertSee('Siswa Milik Saya')
        ->assertDontSee('Siswa Milik Orang Lain')
        ->assertDontSee('X-B');
});

test('filter kelas yang bukan miliknya diabaikan, bukan membuka rekap orang lain', function () {
    $guru = guruDenganAkun();
    $kelasLain = Kelas::factory()->create(['nama_kelas' => 'X-B', 'status' => 'Aktif']);
    siswaDiKelas($kelasLain, 'Siswa Milik Orang Lain');

    $this->actingAs($guru->user)
        ->get(route('guru.laporan', ['kelas' => 'X-B']))
        ->assertOk()
        ->assertDontSee('Siswa Milik Orang Lain');
});

test('filter bulan yang tidak valid diabaikan dan kembali ke bulan ini', function () {
    $guru = guruDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelas->guru()->sync([$guru->id]);

    $payload = $this->actingAs($guru->user)
        ->getJson(route('guru.laporan', ['bulan' => 'bukan-bulan']))
        ->assertOk()
        ->json();

    expect($payload['bulan'])->toBe(now()->format('Y-m'));
});

test('absensi di luar periode bulan terpilih tidak dihitung', function () {
    $guru = guruDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelas->guru()->sync([$guru->id]);

    $siswa = siswaDiKelas($kelas, 'Siswa Absen Bulan Lalu');

    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'SESI-LALU',
        'waktu_mulai' => now()->subMonth(),
        'waktu_selesai' => now()->subMonth(),
        'status' => 'selesai',
    ]);

    Absensi::create([
        'siswa_id' => $siswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now()->subMonth(),
        'status' => 'hadir',
    ]);

    $payload = $this->actingAs($guru->user)
        ->getJson(route('guru.laporan'))
        ->assertOk()
        ->json();

    expect($payload['total']['hadir'])->toBe(0)
        ->and($payload['total']['persentase'])->toBe(0)
        ->and($payload['perSiswa'])->toHaveCount(1);
});

test('siswa tanpa catatan absensi tetap muncul di laporan dengan angka nol', function () {
    $guru = guruDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelas->guru()->sync([$guru->id]);

    siswaDiKelas($kelas, 'Siswa Belum Absen');

    $payload = $this->actingAs($guru->user)
        ->getJson(route('guru.laporan'))
        ->assertOk()
        ->json();

    expect($payload['perSiswa'])->toHaveCount(1)
        ->and($payload['perSiswa'][0]['nama'])->toBe('Siswa Belum Absen')
        ->and($payload['perSiswa'][0]['total'])->toBe(0)
        ->and($payload['perKelas'])->toHaveCount(1);
});

test('export laporan bulanan mengirim csv per kelas dan per siswa', function () {
    $guru = guruDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelas->guru()->sync([$guru->id]);
    siswaDiKelas($kelas, 'Siswa Ekspor');

    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'SESI-EKSPOR',
        'waktu_mulai' => now(),
        'waktu_selesai' => now()->addHour(),
        'status' => 'aktif',
    ]);

    $siswa = Siswa::query()->where('kelas', 'X-A')->firstOrFail();

    Absensi::create([
        'siswa_id' => $siswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now(),
        'status' => 'hadir',
    ]);

    $response = $this->actingAs($guru->user)->get(route('guru.laporan.export'));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    $response->assertDownload();

    $csv = $response->streamedContent();

    expect($csv)->toContain('LAPORAN KEHADIRAN BULANAN')
        ->and($csv)->toContain('RINGKASAN PER KELAS')
        ->and($csv)->toContain('RINGKASAN PER SISWA')
        ->and($csv)->toContain('Siswa Ekspor')
        ->and($csv)->toContain('X-A');
});

test('guru bisa menambah siswa ke kelas yang diampu', function () {
    $guru = guruDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelas->guru()->sync([$guru->id]);

    $this->actingAs($guru->user)
        ->postJson(route('guru.kelola.siswa.store'), [
            'name' => 'Siswa Baru',
            'nis' => '11223344',
            'class' => 'X-A',
            'gender' => 'P',
            'parent' => 'Bapak Siswa',
            'phone' => '08123456789',
        ])
        ->assertCreated()
        ->assertJsonPath('message', 'Siswa berhasil ditambahkan.');

    $siswa = Siswa::query()->where('nisn', '11223344')->firstOrFail();

    expect($siswa->kelas)->toBe('X-A')
        ->and($siswa->user->role)->toBe('Siswa')
        ->and($siswa->user->name)->toBe('Siswa Baru');
});

test('guru tidak bisa menambah siswa ke kelas yang tidak diampu', function () {
    $guru = guruDenganAkun();
    $kelasLain = Kelas::factory()->create(['nama_kelas' => 'X-B', 'status' => 'Aktif']);

    $this->actingAs($guru->user)
        ->postJson(route('guru.kelola.siswa.store'), [
            'name' => 'Siswa Ilegal',
            'nis' => '11223345',
            'class' => 'X-B',
            'gender' => 'L',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('class');

    expect(Siswa::query()->where('nisn', '11223345')->exists())->toBeFalse();
});

test('kelas nonaktif tidak bisa dipakai menambah siswa', function () {
    $guru = guruDenganAkun();
    $arsip = Kelas::factory()->create(['nama_kelas' => 'X-ARSIP', 'status' => 'Arsip']);
    $arsip->guru()->sync([$guru->id]);

    $this->actingAs($guru->user)
        ->postJson(route('guru.kelola.siswa.store'), [
            'name' => 'Siswa Arsip',
            'nis' => '11223346',
            'class' => 'X-ARSIP',
            'gender' => 'L',
        ])
        ->assertStatus(422);
});

test('guru bisa mengubah data siswa di kelasnya', function () {
    $guru = guruDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelas->guru()->sync([$guru->id]);

    $siswa = siswaDiKelas($kelas, 'Nama Lama');

    $this->actingAs($guru->user)
        ->putJson(route('guru.kelola.siswa.update', $siswa), [
            'name' => 'Nama Baru',
            'nis' => $siswa->nisn,
            'class' => 'X-A',
            'gender' => 'P',
            'status' => 'Aktif',
        ])
        ->assertOk()
        ->assertJsonPath('message', 'Data siswa berhasil diperbarui.');

    $siswa->refresh();

    expect($siswa->user->name)->toBe('Nama Baru')
        ->and($siswa->jenis_kelamin)->toBe('P');
});

test('guru tidak bisa mengubah siswa di kelas orang lain', function () {
    $guru = guruDenganAkun();
    $guruLain = guruDenganAkun('Guru Lain');
    $kelasLain = Kelas::factory()->create(['nama_kelas' => 'X-B', 'status' => 'Aktif']);
    $kelasLain->guru()->sync([$guruLain->id]);

    $siswa = siswaDiKelas($kelasLain, 'Siswa Milik Orang Lain');

    $this->actingAs($guru->user)
        ->putJson(route('guru.kelola.siswa.update', $siswa), [
            'name' => 'Berhak Ubah',
            'nis' => $siswa->nisn,
            'class' => 'X-B',
            'gender' => 'L',
            'status' => 'Aktif',
        ])
        ->assertForbidden();

    expect($siswa->fresh()->user->name)->toBe('Siswa Milik Orang Lain');
});

test('guru tidak bisa memindahkan siswa ke kelas yang tidak diampu', function () {
    $guru = guruDenganAkun();
    $kelasMilik = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelasLain = Kelas::factory()->create(['nama_kelas' => 'X-B', 'status' => 'Aktif']);
    $kelasMilik->guru()->sync([$guru->id]);

    $siswa = siswaDiKelas($kelasMilik, 'Siswa Pindah');

    $this->actingAs($guru->user)
        ->putJson(route('guru.kelola.siswa.update', $siswa), [
            'name' => 'Siswa Pindah',
            'nis' => $siswa->nisn,
            'class' => 'X-B',
            'gender' => 'L',
            'status' => 'Aktif',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('class');

    expect($siswa->fresh()->kelas)->toBe('X-A');
});

test('guru bisa menghapus siswa di kelasnya beserta akunnya', function () {
    $guru = guruDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelas->guru()->sync([$guru->id]);

    $siswa = siswaDiKelas($kelas, 'Siswa Dihapus');
    $userId = $siswa->user_id;

    $this->actingAs($guru->user)
        ->deleteJson(route('guru.kelola.siswa.destroy', $siswa))
        ->assertOk()
        ->assertJsonPath('message', 'Siswa berhasil dihapus.');

    expect(Siswa::query()->find($siswa->id))->toBeNull()
        ->and(User::query()->find($userId))->toBeNull();
});

test('guru tidak bisa menghapus siswa di kelas orang lain', function () {
    $guru = guruDenganAkun();
    $kelasLain = Kelas::factory()->create(['nama_kelas' => 'X-B', 'status' => 'Aktif']);

    $siswa = siswaDiKelas($kelasLain, 'Siswa Aman');

    $this->actingAs($guru->user)
        ->deleteJson(route('guru.kelola.siswa.destroy', $siswa))
        ->assertForbidden();

    expect(Siswa::query()->find($siswa->id))->not->toBeNull();
});

test('akun admin tidak bisa mengelola siswa lewat endpoint guru', function () {
    $guru = guruDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelas->guru()->sync([$guru->id]);

    $admin = User::factory()->create(['role' => 'Admin', 'status' => 'Aktif']);

    $this->actingAs($admin)
        ->postJson(route('guru.kelola.siswa.store'), [
            'name' => 'Siswa Dari Admin',
            'nis' => '11223347',
            'class' => 'X-A',
            'gender' => 'L',
        ])
        ->assertForbidden();
});

test('pengunjung tanpa login tidak bisa menambah siswa', function () {
    // Middleware `auth` sudah lebih dulu menolak, jadi jawabannya 401 bukan
    // 403. Yang penting request-nya tidak pernah sampai ke controller.
    $this->postJson(route('guru.kelola.siswa.store'), [
        'name' => 'Siswa Tanpa Login',
        'nis' => '11223348',
        'class' => 'X-A',
        'gender' => 'L',
    ])
        ->assertUnauthorized();
});

test('penambahan siswa memvalidasi nis dan kelas', function () {
    $guru = guruDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelas->guru()->sync([$guru->id]);

    siswaDiKelas($kelas, 'Siswa Existing');

    $this->actingAs($guru->user)
        ->postJson(route('guru.kelola.siswa.store'), [
            'name' => '',
            'nis' => '123',
            'class' => '',
            'gender' => 'X',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'nis', 'class', 'gender']);
});

test('halaman kelola menampilkan kelas dan siswa dari database', function () {
    $guru = guruDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelas->guru()->sync([$guru->id]);

    siswaDiKelas($kelas, 'Siswa Tampil');

    $payload = $this->actingAs($guru->user)
        ->getJson(route('guru.kelola'))
        ->assertOk()
        ->json();

    expect(array_column($payload['kelas'], 'nama'))->toBe(['X-A'])
        ->and($payload['kelas'][0]['siswa'][0]['nama'])->toBe('Siswa Tampil')
        ->and($payload['kelas'][0]['siswa'][0]['gender'])->toBeIn(['L', 'P']);
});

/*
| Progres Absensi. Sumber angka tetap tabel `absensis`, dengan dua hal yang
| membedakannya dari laporan bulanan: periodenya bisa beberapa periode
| sekaligus, dan ada pembanding dengan periode sebelumnya sepanjang yang
| sama supaya guru tahu arah kehadiran, bukan cuma angkanya.
*/

test('progres absensi menghitung kehadiran dari tabel absensi', function () {
    $guru = guruDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelas->guru()->sync([$guru->id]);

    $hadir = siswaDiKelas($kelas, 'Siswa Rajin');
    $alpa = siswaDiKelas($kelas, 'Siswa Bolos');

    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'SESI-PROGRES',
        'waktu_mulai' => now()->startOfDay(),
        'waktu_selesai' => now()->endOfDay(),
        'status' => 'aktif',
    ]);

    foreach ([[$hadir, 'hadir'], [$alpa, 'alpha']] as [$siswa, $status]) {
        Absensi::create([
            'siswa_id' => $siswa->id,
            'sesi_absensi_id' => $sesi->id,
            'waktu_absen' => now(),
            'status' => $status,
        ]);
    }

    $payload = $this->actingAs($guru->user)
        ->getJson(route('guru.laporan', ['tab' => 'periode']))
        ->assertOk()
        ->json();

    expect($payload['total']['hadir'])->toBe(1)
        ->and($payload['total']['alpa'])->toBe(1)
        ->and($payload['total']['persentase'])->toBe(50)
        ->and($payload['perKelas'])->toHaveCount(1)
        ->and($payload['perKelas'][0]['kelas'])->toBe('X-A')
        ->and($payload['perSiswa'])->toHaveCount(2);
});

test('progres absensi memakai json saat diminta', function () {
    $guru = guruDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelas->guru()->sync([$guru->id]);

    $siswa = siswaDiKelas($kelas, 'Siswa Json');

    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'SESI-PROGRES-JSON',
        'waktu_mulai' => now()->startOfDay(),
        'waktu_selesai' => now()->endOfDay(),
        'status' => 'aktif',
    ]);

    Absensi::create([
        'siswa_id' => $siswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now(),
        'status' => 'hadir',
    ]);

    $payload = $this->actingAs($guru->user)
        ->getJson(route('guru.laporan', ['tab' => 'periode']))
        ->assertOk()
        ->json();

    expect($payload['periode'])->toBe('30')
        ->and($payload['jumlahHari'])->toBe(30)
        ->and($payload['hariEfektif'])->toBe(1)
        ->and($payload['perSiswa'][0]['nama'])->toBe('Siswa Json')
        ->and($payload['perluPerhatian'])->toBe([]);
});

test('progres absensi hanya menghitung kelas yang diampu guru', function () {
    $guru = guruDenganAkun();
    $kelasMilik = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelasLain = Kelas::factory()->create(['nama_kelas' => 'X-B', 'status' => 'Aktif']);
    $kelasMilik->guru()->sync([$guru->id]);

    siswaDiKelas($kelasMilik, 'Siswa Milik Saya');
    siswaDiKelas($kelasLain, 'Siswa Milik Orang Lain');

    $this->actingAs($guru->user)
        ->get(route('guru.laporan', ['tab' => 'periode']))
        ->assertOk()
        ->assertSee('Siswa Milik Saya')
        ->assertDontSee('Siswa Milik Orang Lain')
        ->assertDontSee('X-B');
});

test('filter kelas yang bukan miliknya diabaikan, bukan membuka progres orang lain', function () {
    $guru = guruDenganAkun();
    $kelasLain = Kelas::factory()->create(['nama_kelas' => 'X-B', 'status' => 'Aktif']);
    siswaDiKelas($kelasLain, 'Siswa Milik Orang Lain');

    $payload = $this->actingAs($guru->user)
        ->getJson(route('guru.laporan', ['tab' => 'periode', 'kelas' => 'X-B']))
        ->assertOk()
        ->json();

    expect($payload['filterKelas'])->toBeNull()
        ->and($payload['perSiswa'])->toBe([]);
});

test('filter periode yang tidak valid diabaikan dan kembali ke 30 hari', function () {
    $guru = guruDenganAkun();

    $payload = $this->actingAs($guru->user)
        ->getJson(route('guru.laporan', ['tab' => 'periode', 'periode' => 'bukan-periode']))
        ->assertOk()
        ->json();

    expect($payload['periode'])->toBe('30')
        ->and($payload['jumlahHari'])->toBe(30);
});

test('periode progres hanya menghitung absensi di dalam rentangnya', function () {
    $guru = guruDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelas->guru()->sync([$guru->id]);

    $siswa = siswaDiKelas($kelas, 'Siswa Absen Bulan Lalu');

    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'SESI-PROGRES-LALU',
        'waktu_mulai' => now()->subMonths(2),
        'waktu_selesai' => now()->subMonths(2),
        'status' => 'selesai',
    ]);

    Absensi::create([
        'siswa_id' => $siswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now()->subMonths(2),
        'status' => 'hadir',
    ]);

    $payload = $this->actingAs($guru->user)
        ->getJson(route('guru.laporan', ['tab' => 'periode', 'periode' => '30']))
        ->assertOk()
        ->json();

    expect($payload['total']['hadir'])->toBe(0)
        ->and($payload['adaCatatan'])->toBeFalse()
        // Siswa tetap muncul supaya tidak hilang dari halaman.
        ->and($payload['perSiswa'])->toHaveCount(1)
        ->and($payload['perSiswa'][0]['total'])->toBe(0);
});

test('tren harian progres memuat seluruh hari pada periode, termasuk hari kosong', function () {
    $guru = guruDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelas->guru()->sync([$guru->id]);

    $payload = $this->actingAs($guru->user)
        ->getJson(route('guru.laporan', ['tab' => 'periode', 'periode' => '7']))
        ->assertOk()
        ->json();

    expect($payload['trenHarian'])->toHaveCount(7)
        ->and($payload['trenHarian'][0]['tanggal'])->toBe(today()->subDays(6)->toDateString())
        ->and($payload['trenHarian'][6]['tanggal'])->toBe(today()->toDateString())
        ->and($payload['trenHarian'][6]['catatan'])->toBe(0);
});

test('selisih progres membandingkan periode terpilih dengan periode sebelumnya', function () {
    $guru = guruDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelas->guru()->sync([$guru->id]);

    $rajin = siswaDiKelas($kelas, 'Siswa Rajin');
    $bolos = siswaDiKelas($kelas, 'Siswa Bolos');

    $sesiLalu = SesiAbsensi::create([
        'kode_sesi' => 'SESI-PROGRES-SEBELUM',
        'waktu_mulai' => now()->subDays(40),
        'waktu_selesai' => now()->subDays(40),
        'status' => 'selesai',
    ]);

    $sesiIni = SesiAbsensi::create([
        'kode_sesi' => 'SESI-PROGRES-SEKARANG',
        'waktu_mulai' => now()->startOfDay(),
        'waktu_selesai' => now()->endOfDay(),
        'status' => 'aktif',
    ]);

    // Periode sebelumnya: 50% hadir. Periode ini: 100% hadir.
    foreach ([[$rajin, 'hadir'], [$bolos, 'alpha']] as [$siswa, $status]) {
        Absensi::create([
            'siswa_id' => $siswa->id,
            'sesi_absensi_id' => $sesiLalu->id,
            'waktu_absen' => now()->subDays(40),
            'status' => $status,
        ]);
    }

    foreach ([$rajin, $bolos] as $siswa) {
        Absensi::create([
            'siswa_id' => $siswa->id,
            'sesi_absensi_id' => $sesiIni->id,
            'waktu_absen' => now(),
            'status' => 'hadir',
        ]);
    }

    $payload = $this->actingAs($guru->user)
        ->getJson(route('guru.laporan', ['tab' => 'periode', 'periode' => '30']))
        ->assertOk()
        ->json();

    expect($payload['sebelumnya']['persentase'])->toBe(50)
        ->and($payload['total']['persentase'])->toBe(100)
        ->and($payload['selisih'])->toBe(50);
});

test('daftar perlu perhatian hanya berisi siswa di bawah ambang dan punya catatan', function () {
    $guru = guruDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelas->guru()->sync([$guru->id]);

    $rajin = siswaDiKelas($kelas, 'Siswa Rajin');
    $bolos = siswaDiKelas($kelas, 'Siswa Bolos');
    siswaDiKelas($kelas, 'Siswa Tanpa Catatan');

    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'SESI-PROGRES-PERHATIAN',
        'waktu_mulai' => now()->startOfDay(),
        'waktu_selesai' => now()->endOfDay(),
        'status' => 'aktif',
    ]);

    // Rajin hadir 2 dari 2, bolos 1 dari 2 -> 50%.
    foreach ([[$rajin, 'hadir'], [$rajin, 'hadir'], [$bolos, 'hadir'], [$bolos, 'alpha']] as [$siswa, $status]) {
        Absensi::create([
            'siswa_id' => $siswa->id,
            'sesi_absensi_id' => $sesi->id,
            'waktu_absen' => now(),
            'status' => $status,
        ]);
    }

    $payload = $this->actingAs($guru->user)
        ->getJson(route('guru.laporan', ['tab' => 'periode']))
        ->assertOk()
        ->json();

    expect(array_column($payload['perluPerhatian'], 'nama'))->toBe(['Siswa Bolos'])
        // Kehadiran terendah diurutkan paling atas.
        ->and(array_column($payload['perSiswa'], 'nama'))
        ->toBe(['Siswa Bolos', 'Siswa Rajin', 'Siswa Tanpa Catatan']);
});

test('halaman progres absensi menampilkan ringkasan periode di layar', function () {
    $guru = guruDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelas->guru()->sync([$guru->id]);

    siswaDiKelas($kelas, 'Siswa Tampil Progres');

    $this->actingAs($guru->user)
        ->get(route('guru.laporan', ['tab' => 'periode', 'periode' => '7']))
        ->assertOk()
        // Judul halaman tetap "Laporan Bulanan" karena keduanya sudah digabung,
        // sementara isi tabnya yang berbeda.
        ->assertSee('Laporan Bulanan')
        ->assertSee('Progres Absensi')
        ->assertSee('Rata-rata Kehadiran')
        ->assertSee('Tren Kehadiran Harian')
        ->assertSee('Progres per Kelas')
        ->assertSee('Progres per Siswa')
        ->assertSee('Siswa Tampil Progres')
        // Periodenya ikut terbaca di dropdown, bukan hardcoded di view.
        ->assertSee('7 Hari Terakhir')
        ->assertSee('grafikTrenHarian', false);
});

/*
| Rekap bulanan dan progres absensi sudah digabung ke satu halaman
| `guru.laporan`. Bedanya cuma cara memilih periode, jadi keduanya dibedakan
| lewat query `?tab=`, bukan halaman terpisah.
*/

test('laporan bulanan dan progres absensi digabung jadi satu halaman', function () {
    $guru = guruDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelas->guru()->sync([$guru->id]);

    siswaDiKelas($kelas, 'Siswa Gabung');

    // Tanpa `?tab=` halaman membuka rekap bulanan, bukan progres.
    $default = $this->actingAs($guru->user)
        ->get(route('guru.laporan'))
        ->assertOk();

    $default->assertSee('Laporan Bulanan')
        ->assertSee('Periode Bulan')
        ->assertSee('Rekap per Kelas')
        ->assertSee('Rincian per Siswa')
        // Ekspor CSV hanya ada di tab bulanan.
        ->assertSee('Ekspor CSV')
        // Grafik bulanan memakai batang bertumpuk per status.
        ->assertSee('grafikTrenHarian', false);

    // Tab periode membuka isi yang dulu ada di halaman `guru.progres`.
    $periode = $this->actingAs($guru->user)
        ->get(route('guru.laporan', ['tab' => 'periode']))
        ->assertOk();

    $periode->assertSee('Laporan Bulanan')
        ->assertSee('Progres per Kelas')
        ->assertSee('Progres per Siswa')
        ->assertSee('Absensi Hari Ini')
        // Tombol ekspor mengikuti periode bulanan, jadi tidak ada di tab ini.
        ->assertDontSee('Ekspor CSV');
});

test('tab yang tidak dikenal kembali ke rekap bulanan', function () {
    $guru = guruDenganAkun();

    $payload = $this->actingAs($guru->user)
        ->getJson(route('guru.laporan', ['tab' => 'entah']))
        ->assertOk()
        ->json();

    expect($payload['tab'])->toBe('bulanan')
        ->and($payload['sebelumnya'])->toBeNull()
        ->and($payload['selisih'])->toBeNull();
});

test('perpindahan tab membawa pilihan periode yang lain', function () {
    $guru = guruDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelas->guru()->sync([$guru->id]);

    // Link tab harus bring kelas yang sedang difilter, supaya guru tidak
    // kehilangan filter ketika berpindah tab.
    $this->actingAs($guru->user)
        ->get(route('guru.laporan', ['kelas' => 'X-A']))
        ->assertOk()
        ->assertSee('kelas=X-A', false)
        ->assertSee('tab=periode', false);
});

test('halaman progres yang lama sudah tidak ada', function () {
    // Route `guru.progres` dihapus karena sekarang jadi tab di `guru.laporan`.
    $this->get('/dashboard/guru/progres')->assertNotFound();
});

test('ekspor csv tetap memakai periode bulanan meski dari tab progres', function () {
    $guru = guruDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelas->guru()->sync([$guru->id]);

    $siswa = siswaDiKelas($kelas, 'Siswa Ekspor Gabung');

    SesiAbsensi::create([
        'kode_sesi' => 'SESI-EKSPOR-GABUNG',
        'waktu_mulai' => now()->startOfMonth(),
        'waktu_selesai' => now()->endOfMonth(),
        'status' => 'aktif',
    ]);

    Absensi::create([
        'siswa_id' => $siswa->id,
        'sesi_absensi_id' => SesiAbsensi::query()->where('kode_sesi', 'SESI-EKSPOR-GABUNG')->value('id'),
        'waktu_absen' => now(),
        'status' => 'hadir',
    ]);

    $response = $this->actingAs($guru->user)->get(route('guru.laporan.export'));

    $response->assertOk();
    $response->assertDownload();

    expect($response->streamedContent())->toContain('LAPORAN KEHADIRAN BULANAN')
        ->toContain('Siswa Ekspor Gabung');
});

/*
| Real-Time Monitoring. Kondisinya hari ini, sama seperti dashboard, tapi
| halaman ini menyegarkan dirinya sendiri lewat endpoint JSON `guru.dashboard`
| supaya guru tidak perlu reload tiap ada siswa yang memindai QR.
*/

test('real-time monitoring memakai json saat diminta untuk polling', function () {
    $guru = guruDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelas->guru()->sync([$guru->id]);

    $siswa = siswaDiKelas($kelas, 'Siswa Realtime');

    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'SESI-REALTIME-JSON',
        'waktu_mulai' => now()->startOfDay(),
        'waktu_selesai' => now()->endOfDay(),
        'status' => 'aktif',
    ]);

    Absensi::create([
        'siswa_id' => $siswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now(),
        'status' => 'hadir',
    ]);

    $payload = $this->actingAs($guru->user)
        ->getJson(route('guru.realtime'))
        ->assertOk()
        ->json();

    expect(array_column($payload['kelas'], 'nama'))->toBe(['X-A'])
        ->and($payload['kelas'][0]['siswa'][0]['nama'])->toBe('Siswa Realtime')
        // Status hari ini ikut terbawa supaya polling bisa langsung dipakai.
        ->and($payload['kelas'][0]['siswa'][0]['absensi'])->toBe('hadir')
        ->and($payload['kelas'][0]['siswa'][0]['waktu'])->not->toBeNull();
});

test('real-time monitoring hanya mengirim kelas yang diampu guru', function () {
    $guru = guruDenganAkun();
    $kelasMilik = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelasLain = Kelas::factory()->create(['nama_kelas' => 'X-B', 'status' => 'Aktif']);
    $kelasMilik->guru()->sync([$guru->id]);

    siswaDiKelas($kelasMilik, 'Siswa Milik Saya');
    siswaDiKelas($kelasLain, 'Siswa Milik Orang Lain');

    $this->actingAs($guru->user)
        ->get(route('guru.realtime'))
        ->assertOk()
        ->assertDontSee('Siswa Milik Orang Lain')
        ->assertDontSee('X-B');

    $payload = $this->actingAs($guru->user)
        ->getJson(route('guru.realtime'))
        ->assertOk()
        ->json();

    expect(array_column($payload['kelas'], 'nama'))->toBe(['X-A']);
});

test('halaman real-time monitoring punya kendali polling dan feed absensi', function () {
    $guru = guruDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X-A', 'status' => 'Aktif']);
    $kelas->guru()->sync([$guru->id]);

    $this->actingAs($guru->user)
        ->get(route('guru.realtime'))
        ->assertOk()
        ->assertSee('Real-Time Monitoring')
        ->assertSee('Progres Sesi')
        ->assertSee('Absensi Masuk')
        ->assertSee('Daftar Siswa')
        // Poll dan tombol kendalinya harus ada, kalau tidak halaman diam saja.
        ->assertSee('loadRealtimeFromStorage', false)
        ->assertSee('startRealtimeAutoRefresh', false)
        ->assertSee('tombolAutoRefresh', false)
        ->assertSee('feedRealtime', false)
        ->assertSee('tabelRealtime', false)
        // Polling ditembak ke endpoint JSON dashboard, bukan endpoint baru.
        ->assertSee(route('guru.dashboard'), false);
});

test('halaman real-time monitoring menangani guru tanpa kelas', function () {
    $guru = guruDenganAkun();

    $this->actingAs($guru->user)
        ->get(route('guru.realtime'))
        ->assertOk()
        ->assertSee('Belum ada kelas yang diampu', false)
        ->assertSee('realtimeKosongKelas', false);
});

/*
| Mata pelajaran guru harus sama persis di dashboard admin dan dashboard guru.
|
| Dulunya `gurus.mata_pelajaran` adalah teks bebas yang tidak terhubung ke tabel
| `mata_pelajaran`, sementara dashboard guru jatuh ke data contoh `XII.RPL`
| kalau gurunya belum punya kelas. Akibatnya Martha Arinda (Desain Komunikasi
| Visual) terlihat mengajar `XII.RPL` di dashboard guru, padahal dashboard
| admin memakai kode DKV.
*/

test('mata pelajaran guru sama di dashboard admin dan dashboard guru', function () {
    $dkv = mapelDenganKode('DKV', 'Desain Komunikasi Visual');

    $user = User::factory()->create([
        'name' => 'Martha Arinda S.Pd',
        'role' => 'Guru',
        'status' => 'Aktif',
    ]);

    $guru = Guru::factory()->create(['user_id' => $user->id, 'mata_pelajaran_id' => $dkv->id]);

    $kelas = Kelas::factory()->create(['nama_kelas' => 'XII-DKV', 'status' => 'Aktif']);
    $kelas->guru()->sync([$guru->id]);

    // Sisi guru: mapel dan kode mapel dikirim dari baris `mata_pelajaran`.
    $payload = $this->actingAs($guru->user)
        ->getJson(route('guru.dashboard'))
        ->assertOk()
        ->json();

    expect($payload['guru']['mapel'])->toBe('Desain Komunikasi Visual')
        ->and($payload['guru']['kodeMapel'])->toBe('DKV');

    // Sisi admin: halaman guru menampilkan nama & kode dari baris yang sama.
    loginAdmin();

    $this->get(route('cms.teachers'))
        ->assertOk()
        ->assertSee('Desain Komunikasi Visual')
        ->assertSee('DKV')
        ->assertDontSee('XII.RPL');
});

test('dashboard guru tidak lagi menampilkan data contoh kelas milik guru lain', function () {
    // Guru tanpa kelas tidak boleh melihat kelas hardcode XII.RPL beserta
    // siswa contohnya, karena itu membuat dia terlihat mengampu mapel orang.
    $dkv = mapelDenganKode('DKV', 'Desain Komunikasi Visual');

    $user = User::factory()->create([
        'name' => 'Martha Arinda S.Pd',
        'role' => 'Guru',
        'status' => 'Aktif',
    ]);

    $guru = Guru::factory()->create(['user_id' => $user->id, 'mata_pelajaran_id' => $dkv->id]);

    // Bersihkan cache browser supaya test tidak ikut membaca data lama.
    $this->actingAs($guru->user)
        ->get(route('guru.dashboard'))
        ->assertOk()
        ->assertSee('Belum ada kelas yang diampu', false)
        // Nama siswa dari data contoh tidak boleh bocor ke halaman.
        ->assertDontSee('Najla Mutia')
        ->assertDontSee('XII.RPL');

    $payload = $this->actingAs($guru->user)
        ->getJson(route('guru.dashboard'))
        ->assertOk()
        ->json();

    expect($payload['kelas'])->toBe([])
        ->and($payload['guru']['kelas'])->toBe('')
        ->and($payload['guru']['mapel'])->toBe('Desain Komunikasi Visual');
});

test('mengubah mapel guru di admin langsung terlihat di dashboard guru', function () {
    $rpl = mapelDenganKode('RPL', 'Rekayasa Perangkat Lunak');
    $dkv = mapelDenganKode('DKV', 'Desain Komunikasi Visual');

    $user = User::factory()->create([
        'name' => 'Martha Arinda S.Pd',
        'role' => 'Guru',
        'status' => 'Aktif',
    ]);

    $guru = Guru::factory()->create(['user_id' => $user->id, 'mata_pelajaran_id' => $rpl->id]);

    loginAdmin();

    $this->put(route('cms.teachers.update', $guru), [
        'name' => $user->name,
        'nip' => $guru->nip,
        'subject' => $dkv->id,
        'status' => 'Aktif',
    ])->assertSessionHasNoErrors();

    $payload = $this->actingAs($guru->user)
        ->getJson(route('guru.dashboard'))
        ->assertOk()
        ->json();

    expect($payload['guru']['mapel'])->toBe('Desain Komunikasi Visual')
        ->and($payload['guru']['kodeMapel'])->toBe('DKV');
});

test('kolom mapel guru lama diisi dari mapel yang dipilih', function () {
    // Kolom teks `mata_pelajaran` dipertahankan supaya data lama tidak hilang,
    // tapi isinya harus selalu mengikuti mapel yang dipilih lewat form.
    $mapel = MataPelajaran::factory()->create(['nama_mata_pelajaran' => 'Kimia Industri']);

    $guru = Guru::factory()->create(['mata_pelajaran_id' => $mapel->id]);

    expect($guru->fresh()->mata_pelajaran)->toBe('Kimia Industri')
        ->and($guru->namaMataPelajaran())->toBe('Kimia Industri');
});

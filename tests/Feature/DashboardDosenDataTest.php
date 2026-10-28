<?php

use App\Models\Absensi;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\SesiAbsensi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Alur data antara dashboard admin (CMS) dan dashboard dosen.
 *
 * Fokusnya: apa pun yang disimpan dari dashboard CMS harus muncul di dashboard
 * dosen, dan dashboard dosen hanya boleh menampilkan kelas milik dosen yang
 * sedang login.
 */
uses(RefreshDatabase::class);

/**
 * Dosen lengkap dengan akun penggunanya.
 */
function dosenDenganAkun(string $nama = 'Dosen Uji'): Dosen
{
    $user = User::factory()->create([
        'name' => $nama,
        'role' => 'Dosen',
        'status' => 'Aktif',
    ]);

    return Dosen::factory()->create(['user_id' => $user->id]);
}

/**
 * Mahasiswa yang masuk ke sebuah kelas.
 */
function mahasiswaDiKelas(Kelas $kelas, string $nama = 'Mahasiswa Uji'): Mahasiswa
{
    $user = User::factory()->create([
        'name' => $nama,
        'role' => 'Mahasiswa',
        'status' => 'Aktif',
    ]);

    return Mahasiswa::factory()->create([
        'user_id' => $user->id,
        'kelas' => $kelas->nama_kelas,
    ]);
}

test('kelas yang disimpan dari dashboard cms menautkan dosen pengampu', function () {
    loginAdmin();
    $dosen = dosenDenganAkun();

    $this->post(route('cms.kelas.store'), [
        'name' => 'XII-C',
        'level' => 'Semester 3',
        'tahun_akademik' => now()->year,
        'dosens' => [$dosen->id],
    ])->assertRedirect(route('cms.kelas'));

    $kelas = Kelas::query()->where('nama_kelas', 'XII-C')->firstOrFail();

    expect($kelas->dosen()->pluck('dosens.id')->all())->toBe([$dosen->id]);
    expect($dosen->kelasDiampu()->pluck('kelas.id')->all())->toBe([$kelas->id]);
});

test('form ubah kelas mengganti daftar dosen pengampu', function () {
    loginAdmin();
    $dosenAwal = dosenDenganAkun('Dosen Awal');
    $dosenBaru = dosenDenganAkun('Dosen Baru');
    $kelas = Kelas::factory()->create();
    $kelas->dosen()->sync([$dosenAwal->id]);

    $this->put(route('cms.kelas.update', $kelas), [
        'name' => $kelas->nama_kelas,
        'level' => $kelas->tingkat,
        'status' => 'Aktif',
        'dosens' => [$dosenBaru->id],
    ])->assertRedirect(route('cms.kelas'));

    expect($kelas->fresh()->dosen()->pluck('dosens.id')->all())->toBe([$dosenBaru->id]);
});

test('dosen pengampu yang tidak lagi dipilih dilepas dari kelas', function () {
    loginAdmin();
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create();
    $kelas->dosen()->sync([$dosen->id]);

    $this->put(route('cms.kelas.update', $kelas), [
        'name' => $kelas->nama_kelas,
        'level' => $kelas->tingkat,
        'status' => 'Aktif',
        'dosens' => [],
    ])->assertRedirect(route('cms.kelas'));

    expect($kelas->fresh()->dosen()->count())->toBe(0);
});

test('dashboard dosen hanya menampilkan kelas yang diampu dosen tersebut', function () {
    $dosen = dosenDenganAkun();

    $kelasMilik = Kelas::factory()->create(['nama_kelas' => 'Kelas Milik', 'status' => 'Aktif']);
    $kelasLain = Kelas::factory()->create(['nama_kelas' => 'Kelas Lain', 'status' => 'Aktif']);

    $kelasMilik->dosen()->sync([$dosen->id]);

    $this->actingAs($dosen->user)
        ->get(route('dosen.dashboard'))
        ->assertOk()
        ->assertSee('Kelas Milik')
        ->assertDontSee('Kelas Lain');
});

test('mahasiswa yang ditambah dari dashboard cms muncul di dashboard dosen', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    $mahasiswa = mahasiswaDiKelas($kelas, 'Mahasiswa Baru Dari CMS');

    $this->actingAs($dosen->user)
        ->get(route('dosen.dashboard'))
        ->assertOk()
        ->assertSee('Mahasiswa Baru Dari CMS');
});

test('kelas arsip dan kelas nonaktif tidak tampil di dashboard dosen', function () {
    $dosen = dosenDenganAkun();

    $aktif = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $arsip = Kelas::factory()->create(['nama_kelas' => 'Kelas Arsip', 'status' => 'Arsip']);

    $aktif->dosen()->sync([$dosen->id]);
    $arsip->dosen()->sync([$dosen->id]);

    $this->actingAs($dosen->user)
        ->get(route('dosen.dashboard'))
        ->assertOk()
        ->assertSee('Kelas A')
        ->assertDontSee('Kelas Arsip');
});

test('absensi hari ini di database dipakai dashboard dosen, bukan data contoh', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    $hadir = mahasiswaDiKelas($kelas, 'Mahasiswa Hadir');
    $izin = mahasiswaDiKelas($kelas, 'Mahasiswa Izin');

    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'SESI-'.now()->format('Ymd').'-01',
        'waktu_mulai' => now()->subHour(),
        'waktu_selesai' => now()->addHour(),
        'status' => 'aktif',
    ]);

    Absensi::create([
        'mahasiswa_id' => $hadir->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now(),
        'status' => 'hadir',
    ]);

    Absensi::create([
        'mahasiswa_id' => $izin->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now(),
        'status' => 'izin',
    ]);

    $this->actingAs($dosen->user)
        ->get(route('dosen.dashboard'))
        ->assertOk()
        ->assertSee('Mahasiswa Hadir')
        ->assertSee('Mahasiswa Izin');
});

test('absensi bukan hari ini tidak dihitung sebagai kehadiran hari ini', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    $mahasiswa = mahasiswaDiKelas($kelas, 'Mahasiswa Absen Kemarin');

    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'SESI-LAMPAU',
        'waktu_mulai' => now()->subDays(2),
        'waktu_selesai' => now()->subDays(2)->addHour(),
        'status' => 'selesai',
    ]);

    Absensi::create([
        'mahasiswa_id' => $mahasiswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now()->subDays(2),
        'status' => 'hadir',
    ]);

    // Nama mahasiswa tetap tampil, tapi status kehadiran hari ini harus null
    // (belum absen), bukan "hadir" dari absensi dua hari lalu.
    $payload = $this->actingAs($dosen->user)
        ->getJson(route('dosen.dashboard'))
        ->assertOk()
        ->json();

    $this->assertSame('Kelas A', $payload['kelas'][0]['nama']);
    $this->assertSame('Aktif', $payload['kelas'][0]['mahasiswa'][0]['status']);
    $this->assertNull($payload['kelas'][0]['mahasiswa'][0]['absensi']);
});

test('dashboard dosen mengirim data kelas dan mahasiswa ke browser sebagai json', function () {
    $dosen = dosenDenganAkun('Budi Santoso, S.Pd');
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);
    mahasiswaDiKelas($kelas, 'Ani');

    $payload = $this->actingAs($dosen->user)
        ->getJson(route('dosen.dashboard'))
        ->assertOk()
        ->json();

    expect($payload['teraut'])->toBeTrue()
        ->and($payload['dosen']['nama'])->toBe('Budi Santoso, S.Pd')
        ->and($payload['dosen']['kelas'])->toBe('Kelas A')
        ->and($payload['kelas'])->toHaveCount(1)
        ->and($payload['kelas'][0]['nama'])->toBe('Kelas A')
        ->and($payload['kelas'][0]['total'])->toBe(1)
        ->and($payload['kelas'][0]['mahasiswa'][0]['nama'])->toBe('Ani');
});

test('tanpa login dashboard dosen ditolak, bukan menampilkan pratinjau kelas', function () {
    // Dulu halaman ini sengaja membuka "mode pratinjau" untuk pengunjung yang
    // belum login. Sekarang area dosen dilindungi middleware `auth`, jadi data
    // kelas tidak boleh bocor sebelum pengguna masuk.
    Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);

    $this->getJson(route('dosen.dashboard'))
        ->assertUnauthorized();

    $this->get(route('dosen.dashboard'))
        ->assertRedirect(route('login'));
});

test('sidebar dan header memakai nama user yang sedang login', function () {
    $dosen = dosenDenganAkun('Dosen Login');

    $this->actingAs($dosen->user)
        ->get(route('dosen.dashboard'))
        ->assertOk()
        ->assertSee('Dosen Login')
        ->assertSee($dosen->user->email);
});

test('login dosen diarahkan ke dashboard dosen', function () {
    $dosen = dosenDenganAkun();
    $user = $dosen->user;

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('dosen.dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('login admin diarahkan ke dashboard cms', function () {
    $user = User::factory()->create(['role' => 'Admin', 'status' => 'Aktif']);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('cms.dashboard'));
});

test('login mahasiswa langsung ke halaman absensi, bukan ke halaman depan', function () {
    // Mahasiswa punya tempat kegiatannya sendiri, jadi setelah login tidak perlu
    // lewat halaman depan dulu. Sama seperti admin yang langsung masuk ke
    // dashboard CMS dan dosen yang langsung masuk ke dashboard dosen.
    $user = User::factory()->create(['role' => 'Mahasiswa', 'status' => 'Aktif']);

    Mahasiswa::factory()->create(['user_id' => $user->id]);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('absensi.index'));

    $this->assertAuthenticatedAs($user);
});

test('login mahasiswa tanpa profil tetap dikirim ke halaman depan', function () {
    // Halaman absensi menolak akun tanpa baris di tabel `mahasiswa` dengan 403,
    // jadi akun seperti ini harus tetap mendarat di halaman depan.
    $user = User::factory()->create(['role' => 'Mahasiswa', 'status' => 'Aktif']);

    expect($user->mahasiswa)->toBeNull();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('beranda'));
});

test('login dengan password salah ditolak tanpa membocorkan email terdaftar', function () {
    $user = User::factory()->create(['role' => 'Dosen', 'status' => 'Aktif']);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password-salah',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('akun nonaktif tidak bisa login', function () {
    $user = User::factory()->create(['role' => 'Dosen', 'status' => 'Nonaktif']);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('logout mengakhiri sesi', function () {
    $dosen = dosenDenganAkun();

    $this->actingAs($dosen->user)
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

/*
| `POST /login` memakai middleware `guest`. Kalau tidak diberi arah, middleware
| itu mencari route bernama `dashboard`, lalu `home`. Route dashboard di sini
| bernama `cms.dashboard`, sehingga orang yang sudah login lalu submit form login
| lagi akan mendarat di halaman depan `/`, bukan ke halaman tugasnya.
|
| Akun pada test ini sengaja tanpa profil mahasiswa, karena yang diperiksa di sini
| adalah middleware `guest`, bukan tujuan login. Arahan untuk mahasiswa berprofil
| ada di test `login mahasiswa langsung ke halaman absensi` di atas.
*/

test('login saat sudah login diarahkan ke beranda, bukan halaman awal', function () {
    $user = User::factory()->create(['role' => 'Mahasiswa', 'status' => 'Aktif']);

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
    $user = User::factory()->create(['role' => 'Mahasiswa', 'status' => 'Aktif']);

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
    $user = User::factory()->create(['role' => 'Mahasiswa', 'status' => 'Aktif']);

    $this->actingAs($user)->get(route('beranda'))->assertOk();

    $this->assertAuthenticatedAs($user);
});

test('halaman depan menampilkan identitas dan link sesuai peran yang masuk', function () {
    // Beranda sekarang satu halaman untuk semua orang. Navbar publik hanya
    // menampilkan link absensi untuk mahasiswa; area Dosen dan CMS sudah punya
    // sidebar sendiri, jadi tidak perlu link di sini.
    $dosen = dosenDenganAkun('Martha Arinda');

    $this->actingAs($dosen->user)
        ->get(route('beranda'))
        ->assertOk()
        ->assertSee($dosen->user->name)
        ->assertSee('Logout')
        // Dosen masuk lewat sidebar di halaman dashboardnya, bukan lewat navbar.
        ->assertDontSee('>Dashboard Dosen<', false)
        ->assertDontSee('>Dashboard Admin<', false)
        ->assertDontSee('>Rekap<', false);

    $admin = User::factory()->create(['name' => 'Budi Santoso', 'role' => 'Admin', 'status' => 'Aktif']);

    $this->actingAs($admin)
        ->get(route('beranda'))
        ->assertOk()
        ->assertDontSee('>Dashboard Dosen<', false)
        ->assertDontSee('>Dashboard Admin<', false)
        ->assertDontSee('>Rekap<', false);
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
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);
    $mahasiswa = mahasiswaDiKelas($kelas, 'Mahasiswa Tetap Ada');

    $jumlahMahasiswa = Mahasiswa::count();
    $jumlahDosen = Dosen::count();
    $jumlahKelas = Kelas::count();

    $this->actingAs($dosen->user)->get(route('beranda'))->assertOk();

    expect(Mahasiswa::count())->toBe($jumlahMahasiswa)
        ->and(Dosen::count())->toBe($jumlahDosen)
        ->and(Kelas::count())->toBe($jumlahKelas)
        ->and(Mahasiswa::query()->find($mahasiswa->id))->not->toBeNull()
        ->and($dosen->fresh())->not->toBeNull()
        ->and($kelas->fresh()->dosen()->pluck('dosens.id')->all())->toBe([$dosen->id]);
});

test('waktu login terakhir tersimpan di database', function () {
    $dosen = dosenDenganAkun();
    $user = $dosen->user;

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
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'tingkat' => 'Semester 1']);

    $this->put(route('cms.kelas.update', $kelas), [
        'name' => 'Kelas A',
        'level' => 'Semester 1',
        'room' => 'R. 999',
        'status' => 'Aktif',
    ])->assertSessionHasNoErrors();

    expect($kelas->fresh()->ruang)->toBe('R. 999');
});

test('mahasiswa boleh disimpan ulang tanpa mengubah nim nya', function () {
    loginAdmin();
    $mahasiswa = Mahasiswa::factory()->create(['nim' => '12345678']);
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A']);

    $this->put(route('cms.mahasiswa.update', $mahasiswa), [
        'name' => 'Nama Baru',
        'nim' => '12345678',
        'class' => $kelas->nama_kelas,
        'gender' => 'L',
        'status' => 'Aktif',
    ])->assertSessionHasNoErrors();

    expect($mahasiswa->fresh()->user->name)->toBe('Nama Baru');
});

test('dosen boleh disimpan ulang tanpa mengubah nidn nya', function () {
    loginAdmin();
    $dosen = Dosen::factory()->create(['nidn' => '198001010001']);

    $this->put(route('cms.dosens.update', $dosen), [
        'name' => 'Nama Baru',
        'nidn' => '198001010001',
        'subject' => $dosen->mata_kuliah_id,
        'status' => 'Aktif',
    ])->assertSessionHasNoErrors();

    expect($dosen->fresh()->user->name)->toBe('Nama Baru');
});

test('pengguna boleh disimpan ulang tanpa mengubah email nya', function () {
    loginAdmin();
    $user = User::factory()->create(['email' => 'dosen@kampus.ac.id']);

    $this->put(route('cms.users.update', $user), [
        'name' => 'Nama Baru',
        'email' => 'dosen@kampus.ac.id',
        'role' => 'Dosen',
        'status' => 'Aktif',
    ])->assertSessionHasNoErrors();

    expect($user->fresh()->name)->toBe('Nama Baru');
});

/*
| Halaman Kelola Data Presensi. Dosen boleh mengelola mahasiswa di kelas yang diampu,
| dan tidak boleh menyentuh kelas milik dosen lain.
*/

/*
| Laporan Presensi. Semuanya dihitung dari tabel `absensis` yang sama dengan
| rekap admin, lalu dibatasi ke kelas milik dosen yang sedang login.
*/

test('laporan presensi menghitung kehadiran dari tabel absensi', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    $hadir = mahasiswaDiKelas($kelas, 'Mahasiswa Hadir');
    $izin = mahasiswaDiKelas($kelas, 'Mahasiswa Izin');
    $alpa = mahasiswaDiKelas($kelas, 'Mahasiswa Alpa');

    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'SESI-LAPORAN',
        'waktu_mulai' => now()->startOfMonth(),
        'waktu_selesai' => now()->endOfMonth(),
        'status' => 'aktif',
    ]);

    foreach ([[$hadir, 'hadir'], [$izin, 'izin'], [$alpa, 'alpha']] as [$mahasiswa, $status]) {
        Absensi::create([
            'mahasiswa_id' => $mahasiswa->id,
            'sesi_absensi_id' => $sesi->id,
            'waktu_absen' => now(),
            'status' => $status,
        ]);
    }

    $this->actingAs($dosen->user)
        ->get(route('dosen.laporan'))
        ->assertOk()
        ->assertSee('Laporan Presensi')
        ->assertSee('Mahasiswa Hadir')
        ->assertSee('Mahasiswa Alpa');
});

test('laporan presensi memakai json saat diminta', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    $mahasiswa = mahasiswaDiKelas($kelas, 'Mahasiswa Json');

    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'SESI-JSON',
        'waktu_mulai' => now(),
        'waktu_selesai' => now()->addHour(),
        'status' => 'aktif',
    ]);

    Absensi::create([
        'mahasiswa_id' => $mahasiswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now(),
        'status' => 'hadir',
    ]);

    $payload = $this->actingAs($dosen->user)
        ->getJson(route('dosen.laporan'))
        ->assertOk()
        ->json();

    expect($payload['total']['hadir'])->toBe(1)
        ->and($payload['total']['persentase'])->toBe(100)
        ->and($payload['perKelas'][0]['kelas'])->toBe('Kelas A')
        ->and($payload['perMahasiswa'][0]['nama'])->toBe('Mahasiswa Json')
        ->and($payload['perMahasiswa'][0]['hari'])->toBe(1);
});

test('laporan presensi hanya menghitung kelas yang diampu dosen', function () {
    $dosen = dosenDenganAkun();
    $kelasMilik = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelasLain = Kelas::factory()->create(['nama_kelas' => 'Kelas B', 'status' => 'Aktif']);
    $kelasMilik->dosen()->sync([$dosen->id]);

    mahasiswaDiKelas($kelasMilik, 'Mahasiswa Milik Saya');
    mahasiswaDiKelas($kelasLain, 'Mahasiswa Milik Orang Lain');

    $this->actingAs($dosen->user)
        ->get(route('dosen.laporan'))
        ->assertOk()
        ->assertSee('Mahasiswa Milik Saya')
        ->assertDontSee('Mahasiswa Milik Orang Lain')
        ->assertDontSee('Kelas B');
});

test('filter kelas yang bukan miliknya diabaikan, bukan membuka rekap orang lain', function () {
    $dosen = dosenDenganAkun();
    $kelasLain = Kelas::factory()->create(['nama_kelas' => 'Kelas B', 'status' => 'Aktif']);
    mahasiswaDiKelas($kelasLain, 'Mahasiswa Milik Orang Lain');

    $this->actingAs($dosen->user)
        ->get(route('dosen.laporan', ['kelas' => 'Kelas B']))
        ->assertOk()
        ->assertDontSee('Mahasiswa Milik Orang Lain');
});

test('filter bulan yang tidak valid diabaikan dan kembali ke bulan ini', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    $payload = $this->actingAs($dosen->user)
        ->getJson(route('dosen.laporan', ['bulan' => 'bukan-bulan']))
        ->assertOk()
        ->json();

    expect($payload['bulan'])->toBe(now()->format('Y-m'));
});

test('absensi di luar periode bulan terpilih tidak dihitung', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    $mahasiswa = mahasiswaDiKelas($kelas, 'Mahasiswa Absen Bulan Lalu');

    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'SESI-LALU',
        'waktu_mulai' => now()->subMonth(),
        'waktu_selesai' => now()->subMonth(),
        'status' => 'selesai',
    ]);

    Absensi::create([
        'mahasiswa_id' => $mahasiswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now()->subMonth(),
        'status' => 'hadir',
    ]);

    $payload = $this->actingAs($dosen->user)
        ->getJson(route('dosen.laporan'))
        ->assertOk()
        ->json();

    expect($payload['total']['hadir'])->toBe(0)
        ->and($payload['total']['persentase'])->toBe(0)
        ->and($payload['perMahasiswa'])->toHaveCount(1);
});

test('mahasiswa tanpa catatan presensi tetap muncul di laporan dengan angka nol', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    mahasiswaDiKelas($kelas, 'Mahasiswa Belum Presen');

    $payload = $this->actingAs($dosen->user)
        ->getJson(route('dosen.laporan'))
        ->assertOk()
        ->json();

    expect($payload['perMahasiswa'])->toHaveCount(1)
        ->and($payload['perMahasiswa'][0]['nama'])->toBe('Mahasiswa Belum Presen')
        ->and($payload['perMahasiswa'][0]['total'])->toBe(0)
        ->and($payload['perKelas'])->toHaveCount(1);
});

test('export laporan presensi mengirim csv per kelas dan per mahasiswa', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);
    mahasiswaDiKelas($kelas, 'Mahasiswa Ekspor');

    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'SESI-EKSPOR',
        'waktu_mulai' => now(),
        'waktu_selesai' => now()->addHour(),
        'status' => 'aktif',
    ]);

    $mahasiswa = Mahasiswa::query()->where('kelas', 'Kelas A')->firstOrFail();

    Absensi::create([
        'mahasiswa_id' => $mahasiswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now(),
        'status' => 'hadir',
    ]);

    $response = $this->actingAs($dosen->user)->get(route('dosen.laporan.export'));

    $response->assertOk();
    $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    $response->assertDownload();

    $csv = $response->streamedContent();

    expect($csv)->toContain('LAPORAN KEHADIRAN BULANAN')
        ->and($csv)->toContain('RINGKASAN PER KELAS')
        ->and($csv)->toContain('RINGKASAN PER MAHASISWA')
        ->and($csv)->toContain('Mahasiswa Ekspor')
        ->and($csv)->toContain('Kelas A');
});

/**
 * Sesi presensi yang menautkan satu jadwal mata kuliah pada kelas tertentu.
 *
 * Berbeda dengan sesi uji lain yang dibuat tanpa jadwal, sesi ini memakai
 * `jadwal_id` supaya absensinya masuk ke rekap per mata kuliah.
 */
function sesiBermataKuliah(Kelas $kelas, string $mataKuliah): SesiAbsensi
{
    $jadwal = Jadwal::factory()->create([
        'kelas_id' => $kelas->getKey(),
        'mata_kuliah' => $mataKuliah,
    ]);

    return SesiAbsensi::create([
        'jadwal_id' => $jadwal->getKey(),
        'kode_sesi' => 'MK-'.$jadwal->getKey(),
        'waktu_mulai' => now()->startOfDay()->setTime(7, 0),
        'waktu_selesai' => now()->startOfDay()->setTime(9, 0),
        'status' => 'selesai',
    ]);
}

test('laporan mengelompokkan absensi per mata kuliah', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    $mahasiswa = mahasiswaDiKelas($kelas, 'Mahasiswa Per MK');

    $sesi = sesiBermataKuliah($kelas, 'Aljabar Linear');

    Absensi::create([
        'mahasiswa_id' => $mahasiswa->getKey(),
        'sesi_absensi_id' => $sesi->getKey(),
        'waktu_absen' => now(),
        'status' => 'hadir',
    ]);

    $payload = $this->actingAs($dosen->user)
        ->getJson(route('dosen.laporan'))
        ->assertOk()
        ->json();

    expect($payload['perMataKuliah'])->toHaveCount(1)
        ->and($payload['perMataKuliah'][0]['mata_kuliah'])->toBe('Aljabar Linear')
        ->and($payload['perMataKuliah'][0]['sesi'])->toBe(1)
        ->and($payload['perMataKuliah'][0]['hadir'])->toBe(1)
        ->and($payload['perMataKuliah'][0]['persentase'])->toBe(100)
        ->and($payload['total']['sesi'])->toBe(1);
});

test('absensi lama tanpa jadwal masuk ke baris tanpa jadwal kuliah', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    $mahasiswa = mahasiswaDiKelas($kelas, 'Mahasiswa Sesi Lama');

    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'HARIAN-LAMA',
        'waktu_mulai' => now()->startOfDay()->setTime(7, 0),
        'waktu_selesai' => now()->startOfDay()->setTime(9, 0),
        'status' => 'selesai',
    ]);

    Absensi::create([
        'mahasiswa_id' => $mahasiswa->getKey(),
        'sesi_absensi_id' => $sesi->getKey(),
        'waktu_absen' => now(),
        'status' => 'izin',
    ]);

    $payload = $this->actingAs($dosen->user)
        ->getJson(route('dosen.laporan'))
        ->assertOk()
        ->json();

    expect($payload['perMataKuliah'])->toHaveCount(1)
        ->and($payload['perMataKuliah'][0]['mata_kuliah'])->toBe('Tanpa Jadwal Kuliah')
        ->and($payload['perMataKuliah'][0]['izin'])->toBe(1);
});

test('filter mata kuliah membatasi laporan ke satu mata kuliah', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    $mahasiswa = mahasiswaDiKelas($kelas, 'Mahasiswa Dua MK');

    foreach (['Aljabar Linear', 'Basis Data'] as $mataKuliah) {
        $sesi = sesiBermataKuliah($kelas, $mataKuliah);

        Absensi::create([
            'mahasiswa_id' => $mahasiswa->getKey(),
            'sesi_absensi_id' => $sesi->getKey(),
            'waktu_absen' => now(),
            'status' => 'hadir',
        ]);
    }

    $payload = $this->actingAs($dosen->user)
        ->getJson(route('dosen.laporan', ['mata_kuliah' => 'Aljabar Linear']))
        ->assertOk()
        ->json();

    expect($payload['mata_kuliah'])->toBe('Aljabar Linear')
        ->and($payload['perMataKuliah'])->toHaveCount(1)
        ->and($payload['perMataKuliah'][0]['mata_kuliah'])->toBe('Aljabar Linear')
        ->and($payload['total']['hadir'])->toBe(1)
        ->and($payload['pilihanMataKuliah'])->toBe(['Aljabar Linear', 'Basis Data']);

    // Halaman yang dirender juga ikut menyaring tampilannya.
    $this->actingAs($dosen->user)
        ->get(route('dosen.laporan', ['mata_kuliah' => 'Aljabar Linear']))
        ->assertOk()
        ->assertSee('Rekap per Mata Kuliah')
        ->assertSee('Aljabar Linear');
});

test('filter mata kuliah yang bukan pilihan laporan diabaikan', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    $payload = $this->actingAs($dosen->user)
        ->getJson(route('dosen.laporan', ['mata_kuliah' => 'Mata Kuliah Asing']))
        ->assertOk()
        ->json();

    expect($payload['mata_kuliah'])->toBeNull();
});

test('halaman laporan menampilkan rekap per mata kuliah', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    $mahasiswa = mahasiswaDiKelas($kelas, 'Mahasiswa Tampil MK');

    $sesi = sesiBermataKuliah($kelas, 'Aljabar Linear');

    Absensi::create([
        'mahasiswa_id' => $mahasiswa->getKey(),
        'sesi_absensi_id' => $sesi->getKey(),
        'waktu_absen' => now(),
        'status' => 'hadir',
    ]);

    $this->actingAs($dosen->user)
        ->get(route('dosen.laporan'))
        ->assertOk()
        ->assertSee('Rekap per Mata Kuliah')
        ->assertSee('Aljabar Linear')
        ->assertSee('Mata Kuliah');
});

test('export laporan memuat ringkasan per mata kuliah', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    $mahasiswa = mahasiswaDiKelas($kelas, 'Mahasiswa Ekspor MK');

    $sesi = sesiBermataKuliah($kelas, 'Aljabar Linear');

    Absensi::create([
        'mahasiswa_id' => $mahasiswa->getKey(),
        'sesi_absensi_id' => $sesi->getKey(),
        'waktu_absen' => now(),
        'status' => 'hadir',
    ]);

    $csv = $this->actingAs($dosen->user)
        ->get(route('dosen.laporan.export'))
        ->assertOk()
        ->streamedContent();

    expect($csv)
        ->toContain('RINGKASAN PER MATA KULIAH')
        ->toContain('Aljabar Linear')
        ->toContain('"Filter Mata Kuliah";"Semua Mata Kuliah"');
});

test('dosen bisa menambah mahasiswa ke kelas yang diampu', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    $this->actingAs($dosen->user)
        ->postJson(route('dosen.kelola.mahasiswa.store'), [
            'name' => 'Mahasiswa Baru',
            'nim' => '11223344',
            'class' => 'Kelas A',
            'gender' => 'P',
            'parent' => 'Bapak Mahasiswa',
            'phone' => '08123456789',
        ])
        ->assertCreated()
        ->assertJsonPath('message', 'Mahasiswa berhasil ditambahkan.');

    $mahasiswa = Mahasiswa::query()->where('nim', '11223344')->firstOrFail();

    expect($mahasiswa->kelas)->toBe('Kelas A')
        ->and($mahasiswa->user->role)->toBe('Mahasiswa')
        ->and($mahasiswa->user->name)->toBe('Mahasiswa Baru');
});

test('dosen tidak bisa menambah mahasiswa ke kelas yang tidak diampu', function () {
    $dosen = dosenDenganAkun();
    $kelasLain = Kelas::factory()->create(['nama_kelas' => 'Kelas B', 'status' => 'Aktif']);

    $this->actingAs($dosen->user)
        ->postJson(route('dosen.kelola.mahasiswa.store'), [
            'name' => 'Mahasiswa Ilegal',
            'nim' => '11223345',
            'class' => 'Kelas B',
            'gender' => 'L',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('class');

    expect(Mahasiswa::query()->where('nim', '11223345')->exists())->toBeFalse();
});

test('kelas nonaktif tidak bisa dipakai menambah mahasiswa', function () {
    $dosen = dosenDenganAkun();
    $arsip = Kelas::factory()->create(['nama_kelas' => 'Kelas Arsip', 'status' => 'Arsip']);
    $arsip->dosen()->sync([$dosen->id]);

    $this->actingAs($dosen->user)
        ->postJson(route('dosen.kelola.mahasiswa.store'), [
            'name' => 'Mahasiswa Arsip',
            'nim' => '11223346',
            'class' => 'Kelas Arsip',
            'gender' => 'L',
        ])
        ->assertStatus(422);
});

test('dosen bisa mengubah data mahasiswa di kelasnya', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    $mahasiswa = mahasiswaDiKelas($kelas, 'Nama Lama');

    $this->actingAs($dosen->user)
        ->putJson(route('dosen.kelola.mahasiswa.update', $mahasiswa), [
            'name' => 'Nama Baru',
            'nim' => $mahasiswa->nim,
            'class' => 'Kelas A',
            'gender' => 'P',
            'status' => 'Aktif',
        ])
        ->assertOk()
        ->assertJsonPath('message', 'Data mahasiswa berhasil diperbarui.');

    $mahasiswa->refresh();

    expect($mahasiswa->user->name)->toBe('Nama Baru')
        ->and($mahasiswa->jenis_kelamin)->toBe('P');
});

test('dosen tidak bisa mengubah mahasiswa di kelas orang lain', function () {
    $dosen = dosenDenganAkun();
    $dosenLain = dosenDenganAkun('Dosen Lain');
    $kelasLain = Kelas::factory()->create(['nama_kelas' => 'Kelas B', 'status' => 'Aktif']);
    $kelasLain->dosen()->sync([$dosenLain->id]);

    $mahasiswa = mahasiswaDiKelas($kelasLain, 'Mahasiswa Milik Orang Lain');

    $this->actingAs($dosen->user)
        ->putJson(route('dosen.kelola.mahasiswa.update', $mahasiswa), [
            'name' => 'Berhak Ubah',
            'nim' => $mahasiswa->nim,
            'class' => 'Kelas B',
            'gender' => 'L',
            'status' => 'Aktif',
        ])
        ->assertForbidden();

    expect($mahasiswa->fresh()->user->name)->toBe('Mahasiswa Milik Orang Lain');
});

test('dosen tidak bisa memindahkan mahasiswa ke kelas yang tidak diampu', function () {
    $dosen = dosenDenganAkun();
    $kelasMilik = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelasLain = Kelas::factory()->create(['nama_kelas' => 'Kelas B', 'status' => 'Aktif']);
    $kelasMilik->dosen()->sync([$dosen->id]);

    $mahasiswa = mahasiswaDiKelas($kelasMilik, 'Mahasiswa Pindah');

    $this->actingAs($dosen->user)
        ->putJson(route('dosen.kelola.mahasiswa.update', $mahasiswa), [
            'name' => 'Mahasiswa Pindah',
            'nim' => $mahasiswa->nim,
            'class' => 'Kelas B',
            'gender' => 'L',
            'status' => 'Aktif',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors('class');

    expect($mahasiswa->fresh()->kelas)->toBe('Kelas A');
});

test('dosen bisa menghapus mahasiswa di kelasnya beserta akunnya', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    $mahasiswa = mahasiswaDiKelas($kelas, 'Mahasiswa Dihapus');
    $userId = $mahasiswa->user_id;

    $this->actingAs($dosen->user)
        ->deleteJson(route('dosen.kelola.mahasiswa.destroy', $mahasiswa))
        ->assertOk()
        ->assertJsonPath('message', 'Mahasiswa berhasil dihapus.');

    expect(Mahasiswa::query()->find($mahasiswa->id))->toBeNull()
        ->and(User::query()->find($userId))->toBeNull();
});

test('dosen tidak bisa menghapus mahasiswa di kelas orang lain', function () {
    $dosen = dosenDenganAkun();
    $kelasLain = Kelas::factory()->create(['nama_kelas' => 'Kelas B', 'status' => 'Aktif']);

    $mahasiswa = mahasiswaDiKelas($kelasLain, 'Mahasiswa Aman');

    $this->actingAs($dosen->user)
        ->deleteJson(route('dosen.kelola.mahasiswa.destroy', $mahasiswa))
        ->assertForbidden();

    expect(Mahasiswa::query()->find($mahasiswa->id))->not->toBeNull();
});

test('akun admin tidak bisa mengelola mahasiswa lewat endpoint dosen', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    $admin = User::factory()->create(['role' => 'Admin', 'status' => 'Aktif']);

    $this->actingAs($admin)
        ->postJson(route('dosen.kelola.mahasiswa.store'), [
            'name' => 'Mahasiswa Dari Admin',
            'nim' => '11223347',
            'class' => 'Kelas A',
            'gender' => 'L',
        ])
        ->assertForbidden();
});

test('pengunjung tanpa login tidak bisa menambah mahasiswa', function () {
    // Middleware `auth` sudah lebih dulu menolak, jadi jawabannya 401 bukan
    // 403. Yang penting request-nya tidak pernah sampai ke controller.
    $this->postJson(route('dosen.kelola.mahasiswa.store'), [
        'name' => 'Mahasiswa Tanpa Login',
        'nim' => '11223348',
        'class' => 'Kelas A',
        'gender' => 'L',
    ])
        ->assertUnauthorized();
});

test('penambahan mahasiswa memvalidasi nim dan kelas', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    mahasiswaDiKelas($kelas, 'Mahasiswa Existing');

    $this->actingAs($dosen->user)
        ->postJson(route('dosen.kelola.mahasiswa.store'), [
            'name' => '',
            'nim' => '123',
            'class' => '',
            'gender' => 'X',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'nim', 'class', 'gender']);
});

test('halaman tambah mahasiswa tampil dan membatasi pilihan kelas ke yang diampu', function () {
    $dosen = dosenDenganAkun();
    $kelasDiampu = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    Kelas::factory()->create(['nama_kelas' => 'Kelas B', 'status' => 'Aktif']);
    $kelasDiampu->dosen()->sync([$dosen->id]);

    $this->actingAs($dosen->user)
        ->get(route('dosen.kelola.mahasiswa.create'))
        ->assertOk()
        ->assertSee('Tambah Mahasiswa')
        // Formnya halaman penuh yang men-post langsung ke endpoint simpan,
        // bukan modal mengambang.
        ->assertSee(route('dosen.kelola.mahasiswa.store'), false)
        // Pilihan kelasnya hanya kelas yang diampu, bukan seluruh kampus.
        ->assertSee('Kelas A')
        ->assertDontSee('Kelas B');
});

test('form halaman tambah membalas redirect dan menyimpan mahasiswa', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    // Permintaan form (bukan JSON) harus dibalas halaman kelola, bukan JSON,
    // supaya pengguna langsung melihat daftar terbarunya.
    $this->actingAs($dosen->user)
        ->post(route('dosen.kelola.mahasiswa.store'), [
            'name' => 'Mahasiswa Form',
            'nim' => '55667788',
            'class' => 'Kelas A',
            'gender' => 'L',
            'parent' => 'Bapak Form',
            'phone' => '0811111111',
        ])
        ->assertRedirect(route('dosen.kelola'))
        ->assertSessionHas('success')
        ->assertSessionHas('mahasiswa_baru');

    $mahasiswa = Mahasiswa::query()->where('nim', '55667788')->firstOrFail();

    expect($mahasiswa->kelas)->toBe('Kelas A')
        ->and($mahasiswa->user->role)->toBe('Mahasiswa')
        ->and($mahasiswa->user->name)->toBe('Mahasiswa Form');
});

test('halaman kelola menampilkan kelas dan mahasiswa dari database', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    mahasiswaDiKelas($kelas, 'Mahasiswa Tampil');

    $payload = $this->actingAs($dosen->user)
        ->getJson(route('dosen.kelola'))
        ->assertOk()
        ->json();

    expect(array_column($payload['kelas'], 'nama'))->toBe(['Kelas A'])
        ->and($payload['kelas'][0]['mahasiswa'][0]['nama'])->toBe('Mahasiswa Tampil')
        ->and($payload['kelas'][0]['mahasiswa'][0]['gender'])->toBeIn(['L', 'P']);
});

/*
| Progres Presensi. Sumber angka tetap tabel `absensis`, dengan dua hal yang
| membedakannya dari tab rekap bulanan: periodenya bisa beberapa periode
| sekaligus, dan ada pembanding dengan periode sebelumnya sepanjang yang
| sama supaya dosen tahu arah kehadiran, bukan cuma angkanya.
*/

test('progres absensi menghitung kehadiran dari tabel absensi', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    $hadir = mahasiswaDiKelas($kelas, 'Mahasiswa Rajin');
    $alpa = mahasiswaDiKelas($kelas, 'Mahasiswa Bolos');

    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'SESI-PROGRES',
        'waktu_mulai' => now()->startOfDay(),
        'waktu_selesai' => now()->endOfDay(),
        'status' => 'aktif',
    ]);

    foreach ([[$hadir, 'hadir'], [$alpa, 'alpha']] as [$mahasiswa, $status]) {
        Absensi::create([
            'mahasiswa_id' => $mahasiswa->id,
            'sesi_absensi_id' => $sesi->id,
            'waktu_absen' => now(),
            'status' => $status,
        ]);
    }

    $payload = $this->actingAs($dosen->user)
        ->getJson(route('dosen.laporan', ['tab' => 'periode']))
        ->assertOk()
        ->json();

    expect($payload['total']['hadir'])->toBe(1)
        ->and($payload['total']['alpa'])->toBe(1)
        ->and($payload['total']['persentase'])->toBe(50)
        ->and($payload['perKelas'])->toHaveCount(1)
        ->and($payload['perKelas'][0]['kelas'])->toBe('Kelas A')
        ->and($payload['perMahasiswa'])->toHaveCount(2);
});

test('progres absensi memakai json saat diminta', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    $mahasiswa = mahasiswaDiKelas($kelas, 'Mahasiswa Json');

    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'SESI-PROGRES-JSON',
        'waktu_mulai' => now()->startOfDay(),
        'waktu_selesai' => now()->endOfDay(),
        'status' => 'aktif',
    ]);

    Absensi::create([
        'mahasiswa_id' => $mahasiswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now(),
        'status' => 'hadir',
    ]);

    $payload = $this->actingAs($dosen->user)
        ->getJson(route('dosen.laporan', ['tab' => 'periode']))
        ->assertOk()
        ->json();

    expect($payload['periode'])->toBe('30')
        ->and($payload['jumlahHari'])->toBe(30)
        ->and($payload['hariEfektif'])->toBe(1)
        ->and($payload['perMahasiswa'][0]['nama'])->toBe('Mahasiswa Json')
        ->and($payload['perluPerhatian'])->toBe([]);
});

test('progres absensi hanya menghitung kelas yang diampu dosen', function () {
    $dosen = dosenDenganAkun();
    $kelasMilik = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelasLain = Kelas::factory()->create(['nama_kelas' => 'Kelas B', 'status' => 'Aktif']);
    $kelasMilik->dosen()->sync([$dosen->id]);

    mahasiswaDiKelas($kelasMilik, 'Mahasiswa Milik Saya');
    mahasiswaDiKelas($kelasLain, 'Mahasiswa Milik Orang Lain');

    $this->actingAs($dosen->user)
        ->get(route('dosen.laporan', ['tab' => 'periode']))
        ->assertOk()
        ->assertSee('Mahasiswa Milik Saya')
        ->assertDontSee('Mahasiswa Milik Orang Lain')
        ->assertDontSee('Kelas B');
});

test('filter kelas yang bukan miliknya diabaikan, bukan membuka progres orang lain', function () {
    $dosen = dosenDenganAkun();
    $kelasLain = Kelas::factory()->create(['nama_kelas' => 'Kelas B', 'status' => 'Aktif']);
    mahasiswaDiKelas($kelasLain, 'Mahasiswa Milik Orang Lain');

    $payload = $this->actingAs($dosen->user)
        ->getJson(route('dosen.laporan', ['tab' => 'periode', 'kelas' => 'Kelas B']))
        ->assertOk()
        ->json();

    expect($payload['filterKelas'])->toBeNull()
        ->and($payload['perMahasiswa'])->toBe([]);
});

test('filter periode yang tidak valid diabaikan dan kembali ke 30 hari', function () {
    $dosen = dosenDenganAkun();

    $payload = $this->actingAs($dosen->user)
        ->getJson(route('dosen.laporan', ['tab' => 'periode', 'periode' => 'bukan-periode']))
        ->assertOk()
        ->json();

    expect($payload['periode'])->toBe('30')
        ->and($payload['jumlahHari'])->toBe(30);
});

test('periode progres hanya menghitung absensi di dalam rentangnya', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    $mahasiswa = mahasiswaDiKelas($kelas, 'Mahasiswa Absen Bulan Lalu');

    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'SESI-PROGRES-LALU',
        'waktu_mulai' => now()->subMonths(2),
        'waktu_selesai' => now()->subMonths(2),
        'status' => 'selesai',
    ]);

    Absensi::create([
        'mahasiswa_id' => $mahasiswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now()->subMonths(2),
        'status' => 'hadir',
    ]);

    $payload = $this->actingAs($dosen->user)
        ->getJson(route('dosen.laporan', ['tab' => 'periode', 'periode' => '30']))
        ->assertOk()
        ->json();

    expect($payload['total']['hadir'])->toBe(0)
        ->and($payload['adaCatatan'])->toBeFalse()
        // Mahasiswa tetap muncul supaya tidak hilang dari halaman.
        ->and($payload['perMahasiswa'])->toHaveCount(1)
        ->and($payload['perMahasiswa'][0]['total'])->toBe(0);
});

test('tren harian progres memuat seluruh hari pada periode, termasuk hari kosong', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    $payload = $this->actingAs($dosen->user)
        ->getJson(route('dosen.laporan', ['tab' => 'periode', 'periode' => '7']))
        ->assertOk()
        ->json();

    expect($payload['trenHarian'])->toHaveCount(7)
        ->and($payload['trenHarian'][0]['tanggal'])->toBe(today()->subDays(6)->toDateString())
        ->and($payload['trenHarian'][6]['tanggal'])->toBe(today()->toDateString())
        ->and($payload['trenHarian'][6]['catatan'])->toBe(0);
});

test('selisih progres membandingkan periode terpilih dengan periode sebelumnya', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    $rajin = mahasiswaDiKelas($kelas, 'Mahasiswa Rajin');
    $bolos = mahasiswaDiKelas($kelas, 'Mahasiswa Bolos');

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
    foreach ([[$rajin, 'hadir'], [$bolos, 'alpha']] as [$mahasiswa, $status]) {
        Absensi::create([
            'mahasiswa_id' => $mahasiswa->id,
            'sesi_absensi_id' => $sesiLalu->id,
            'waktu_absen' => now()->subDays(40),
            'status' => $status,
        ]);
    }

    foreach ([$rajin, $bolos] as $mahasiswa) {
        Absensi::create([
            'mahasiswa_id' => $mahasiswa->id,
            'sesi_absensi_id' => $sesiIni->id,
            'waktu_absen' => now(),
            'status' => 'hadir',
        ]);
    }

    $payload = $this->actingAs($dosen->user)
        ->getJson(route('dosen.laporan', ['tab' => 'periode', 'periode' => '30']))
        ->assertOk()
        ->json();

    expect($payload['sebelumnya']['persentase'])->toBe(50)
        ->and($payload['total']['persentase'])->toBe(100)
        ->and($payload['selisih'])->toBe(50);
});

test('daftar perlu perhatian hanya berisi mahasiswa di bawah ambang dan punya catatan', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    $rajin = mahasiswaDiKelas($kelas, 'Mahasiswa Rajin');
    $bolos = mahasiswaDiKelas($kelas, 'Mahasiswa Bolos');
    mahasiswaDiKelas($kelas, 'Mahasiswa Tanpa Catatan');

    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'SESI-PROGRES-PERHATIAN',
        'waktu_mulai' => now()->startOfDay(),
        'waktu_selesai' => now()->endOfDay(),
        'status' => 'aktif',
    ]);

    // Rajin hadir 2 dari 2, bolos 1 dari 2 -> 50%.
    foreach ([[$rajin, 'hadir'], [$rajin, 'hadir'], [$bolos, 'hadir'], [$bolos, 'alpha']] as [$mahasiswa, $status]) {
        Absensi::create([
            'mahasiswa_id' => $mahasiswa->id,
            'sesi_absensi_id' => $sesi->id,
            'waktu_absen' => now(),
            'status' => $status,
        ]);
    }

    $payload = $this->actingAs($dosen->user)
        ->getJson(route('dosen.laporan', ['tab' => 'periode']))
        ->assertOk()
        ->json();

    expect(array_column($payload['perluPerhatian'], 'nama'))->toBe(['Mahasiswa Bolos'])
        // Kehadiran terendah diurutkan paling atas.
        ->and(array_column($payload['perMahasiswa'], 'nama'))
        ->toBe(['Mahasiswa Bolos', 'Mahasiswa Rajin', 'Mahasiswa Tanpa Catatan']);
});

test('halaman progres absensi menampilkan ringkasan periode di layar', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    mahasiswaDiKelas($kelas, 'Mahasiswa Tampil Progres');

    $this->actingAs($dosen->user)
        ->get(route('dosen.laporan', ['tab' => 'periode', 'periode' => '7']))
        ->assertOk()
        // Judul halaman tetap "Laporan Presensi" karena keduanya sudah digabung,
        // sementara isi tabnya yang berbeda.
        ->assertSee('Laporan Presensi')
        ->assertSee('Progres Presensi')
        ->assertSee('Tren Kehadiran Harian')
        ->assertSee('Ringkasan per Kelas')
        ->assertSee('Progres per Mahasiswa')
        ->assertSee('Mahasiswa Tampil Progres')
        // Periodenya ikut terbaca di dropdown, bukan hardcoded di view.
        ->assertSee('7 Hari Terakhir')
        ->assertSee('grafikTrenHarian', false);
});

/*
| Rekap bulanan dan progres absensi sudah digabung ke satu halaman
| `dosen.laporan`. Bedanya cuma cara memilih periode, jadi keduanya dibedakan
| lewat query `?tab=`, bukan halaman terpisah.
*/

test('laporan presensi dan progres absensi digabung jadi satu halaman', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    mahasiswaDiKelas($kelas, 'Mahasiswa Gabung');

    // Tanpa `?tab=` halaman membuka rekap bulanan, bukan progres.
    $default = $this->actingAs($dosen->user)
        ->get(route('dosen.laporan'))
        ->assertOk();

    $default->assertSee('Laporan Presensi')
        ->assertSee('Periode Bulan')
        ->assertSee('Ringkasan per Kelas')
        ->assertSee('Rincian per Mahasiswa')
        // Ekspor CSV hanya ada di tab bulanan.
        ->assertSee('Ekspor CSV')
        // Grafik bulanan memakai batang bertumpuk per status.
        ->assertSee('grafikTrenHarian', false);

    // Tab periode membuka isi yang dulu ada di halaman `dosen.progres`.
    $periode = $this->actingAs($dosen->user)
        ->get(route('dosen.laporan', ['tab' => 'periode']))
        ->assertOk();

    $periode->assertSee('Laporan Presensi')
        ->assertSee('Ringkasan per Kelas')
        ->assertSee('Progres per Mahasiswa')
        ->assertSee('Presensi Hari Ini')
        // Tombol ekspor mengikuti periode bulanan, jadi tidak ada di tab ini.
        ->assertDontSee('Ekspor CSV');
});

test('tab yang tidak dikenal kembali ke rekap bulanan', function () {
    $dosen = dosenDenganAkun();

    $payload = $this->actingAs($dosen->user)
        ->getJson(route('dosen.laporan', ['tab' => 'entah']))
        ->assertOk()
        ->json();

    expect($payload['tab'])->toBe('bulanan')
        ->and($payload['sebelumnya'])->toBeNull()
        ->and($payload['selisih'])->toBeNull();
});

test('perpindahan tab membawa pilihan periode yang lain', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    // Link tab harus bring kelas yang sedang difilter, supaya dosen tidak
    // kehilangan filter ketika berpindah tab.
    $this->actingAs($dosen->user)
        ->get(route('dosen.laporan', ['kelas' => 'Kelas A']))
        ->assertOk()
        ->assertSee('kelas=Kelas%20A', false)
        ->assertSee('tab=periode', false);
});

test('halaman progres yang lama sudah tidak ada', function () {
    // Route `dosen.progres` dihapus karena sekarang jadi tab di `dosen.laporan`.
    $this->get('/dashboard/dosen/progres')->assertNotFound();
});

test('ekspor csv tetap memakai periode bulanan meski dari tab progres', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    $mahasiswa = mahasiswaDiKelas($kelas, 'Mahasiswa Ekspor Gabung');

    SesiAbsensi::create([
        'kode_sesi' => 'SESI-EKSPOR-GABUNG',
        'waktu_mulai' => now()->startOfMonth(),
        'waktu_selesai' => now()->endOfMonth(),
        'status' => 'aktif',
    ]);

    Absensi::create([
        'mahasiswa_id' => $mahasiswa->id,
        'sesi_absensi_id' => SesiAbsensi::query()->where('kode_sesi', 'SESI-EKSPOR-GABUNG')->value('id'),
        'waktu_absen' => now(),
        'status' => 'hadir',
    ]);

    $response = $this->actingAs($dosen->user)->get(route('dosen.laporan.export'));

    $response->assertOk();
    $response->assertDownload();

    expect($response->streamedContent())->toContain('LAPORAN KEHADIRAN BULANAN')
        ->toContain('Mahasiswa Ekspor Gabung');
});

/*
| Pemantauan real-time sudah jadi bagian dari dashboard dosen. Kondisinya hari
| ini sama seperti ringkasan, tapi halaman ini menyegarkan dirinya sendiri lewat
| endpoint JSON `dosen.dashboard` supaya dosen tidak perlu reload tiap ada mahasiswa
| yang absen.
*/

test('dashboard dosen memakai json saat diminta untuk polling', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    $mahasiswa = mahasiswaDiKelas($kelas, 'Mahasiswa Realtime');

    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'SESI-REALTIME-JSON',
        'waktu_mulai' => now()->startOfDay(),
        'waktu_selesai' => now()->endOfDay(),
        'status' => 'aktif',
    ]);

    Absensi::create([
        'mahasiswa_id' => $mahasiswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now(),
        'status' => 'hadir',
    ]);

    $payload = $this->actingAs($dosen->user)
        ->getJson(route('dosen.dashboard'))
        ->assertOk()
        ->json();

    expect(array_column($payload['kelas'], 'nama'))->toBe(['Kelas A'])
        ->and($payload['kelas'][0]['mahasiswa'][0]['nama'])->toBe('Mahasiswa Realtime')
        // Status hari ini ikut terbawa supaya polling bisa langsung dipakai.
        ->and($payload['kelas'][0]['mahasiswa'][0]['absensi'])->toBe('hadir')
        ->and($payload['kelas'][0]['mahasiswa'][0]['waktu'])->not->toBeNull();
});

test('dashboard dosen hanya mengirim kelas yang diampu dosen', function () {
    $dosen = dosenDenganAkun();
    $kelasMilik = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelasLain = Kelas::factory()->create(['nama_kelas' => 'Kelas B', 'status' => 'Aktif']);
    $kelasMilik->dosen()->sync([$dosen->id]);

    mahasiswaDiKelas($kelasMilik, 'Mahasiswa Milik Saya');
    mahasiswaDiKelas($kelasLain, 'Mahasiswa Milik Orang Lain');

    $this->actingAs($dosen->user)
        ->get(route('dosen.dashboard'))
        ->assertOk()
        ->assertDontSee('Mahasiswa Milik Orang Lain')
        ->assertDontSee('Kelas B');

    $payload = $this->actingAs($dosen->user)
        ->getJson(route('dosen.dashboard'))
        ->assertOk()
        ->json();

    expect(array_column($payload['kelas'], 'nama'))->toBe(['Kelas A']);
});

test('dashboard dosen punya kendali polling dan feed absensi', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    $this->actingAs($dosen->user)
        ->get(route('dosen.dashboard'))
        ->assertOk()
        // Ringkasan dari halaman dashboard lama.
        ->assertSee('Tren Kehadiran Bulanan')
        ->assertSee('Ringkasan Hari Ini')
        ->assertSee('Progres per Kelas')
        ->assertSee('Daftar Presensi Mahasiswa')
        // Pemantauan dari halaman real-time monitoring yang sudah digabung.
        ->assertSee('Presensi Masuk')
        // Poll dan tombol kendalinya harus ada, kalau tidak halaman diam saja.
        ->assertSee('loadRealtimeFromStorage', false)
        ->assertSee('startRealtimeAutoRefresh', false)
        ->assertSee('tombolAutoRefresh', false)
        ->assertSee('tombolRefresh', false)
        ->assertSee('feedAbsensi', false)
        ->assertSee('tabelAbsensi', false)
        ->assertSee('toastAbsensi', false)
        // Polling ditembak ke endpoint JSON halaman ini sendiri.
        ->assertSee(route('dosen.dashboard'), false);
});

test('dashboard dosen menangani dosen tanpa kelas', function () {
    $dosen = dosenDenganAkun();

    $this->actingAs($dosen->user)
        ->get(route('dosen.dashboard'))
        ->assertOk()
        ->assertSee('Belum ada kelas yang diampu', false)
        ->assertSee('kosongKelas', false);
});

/*
| Mata pelajaran dosen harus sama persis di dashboard admin dan dashboard dosen.
|
| Dulunya `dosens.mata_kuliah` adalah teks bebas yang tidak terhubung ke tabel
| `mata_kuliah`, sementara dashboard dosen jatuh ke data contoh `XII.RPL`
| kalau dosennya belum punya kelas. Akibatnya Martha Arinda (Desain Komunikasi
| Visual) terlihat mengajar `XII.RPL` di dashboard dosen, padahal dashboard
| admin memakai kode DKV.
*/

test('mata kuliah dosen sama di dashboard admin dan dashboard dosen', function () {
    $dkv = mataKuliahDenganKode('DKV', 'Desain Komunikasi Visual');

    $user = User::factory()->create([
        'name' => 'Martha Arinda S.Pd',
        'role' => 'Dosen',
        'status' => 'Aktif',
    ]);

    $dosen = Dosen::factory()->create(['user_id' => $user->id, 'mata_kuliah_id' => $dkv->id]);

    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas DKV', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    // Sisi dosen: mata kuliah dan kode mata kuliah dikirim dari baris `mata_kuliah`.
    $payload = $this->actingAs($dosen->user)
        ->getJson(route('dosen.dashboard'))
        ->assertOk()
        ->json();

    expect($payload['dosen']['mataKuliah'])->toBe('Desain Komunikasi Visual')
        ->and($payload['dosen']['kodeMataKuliah'])->toBe('DKV');

    // Sisi admin: halaman dosen menampilkan nama & kode dari baris yang sama.
    loginAdmin();

    $this->get(route('cms.dosens'))
        ->assertOk()
        ->assertSee('Desain Komunikasi Visual')
        ->assertSee('DKV')
        ->assertDontSee('Kelas RPL');
});

test('dashboard dosen tidak lagi menampilkan data contoh kelas milik dosen lain', function () {
    // Dosen tanpa kelas tidak boleh melihat kelas hardcode XII.RPL beserta
    // mahasiswa contohnya, karena itu membuat dia terlihat mengampu mata kuliah orang.
    $dkv = mataKuliahDenganKode('DKV', 'Desain Komunikasi Visual');

    $user = User::factory()->create([
        'name' => 'Martha Arinda S.Pd',
        'role' => 'Dosen',
        'status' => 'Aktif',
    ]);

    $dosen = Dosen::factory()->create(['user_id' => $user->id, 'mata_kuliah_id' => $dkv->id]);

    // Bersihkan cache browser supaya test tidak ikut membaca data lama.
    $this->actingAs($dosen->user)
        ->get(route('dosen.dashboard'))
        ->assertOk()
        ->assertSee('Belum ada kelas yang diampu', false)
        // Nama mahasiswa dari data contoh tidak boleh bocor ke halaman.
        ->assertDontSee('Najla Mutia')
        ->assertDontSee('Kelas RPL');

    $payload = $this->actingAs($dosen->user)
        ->getJson(route('dosen.dashboard'))
        ->assertOk()
        ->json();

    expect($payload['kelas'])->toBe([])
        ->and($payload['dosen']['kelas'])->toBe('')
        ->and($payload['dosen']['mataKuliah'])->toBe('Desain Komunikasi Visual');
});

test('mengubah mata kuliah dosen di admin langsung terlihat di dashboard dosen', function () {
    $rpl = mataKuliahDenganKode('RPL', 'Rekayasa Perangkat Lunak');
    $dkv = mataKuliahDenganKode('DKV', 'Desain Komunikasi Visual');

    $user = User::factory()->create([
        'name' => 'Martha Arinda S.Pd',
        'role' => 'Dosen',
        'status' => 'Aktif',
    ]);

    $dosen = Dosen::factory()->create(['user_id' => $user->id, 'mata_kuliah_id' => $rpl->id]);

    loginAdmin();

    $this->put(route('cms.dosens.update', $dosen), [
        'name' => $user->name,
        'nidn' => $dosen->nidn,
        'subject' => $dkv->id,
        'status' => 'Aktif',
    ])->assertSessionHasNoErrors();

    $payload = $this->actingAs($dosen->user)
        ->getJson(route('dosen.dashboard'))
        ->assertOk()
        ->json();

    expect($payload['dosen']['mataKuliah'])->toBe('Desain Komunikasi Visual')
        ->and($payload['dosen']['kodeMataKuliah'])->toBe('DKV');
});

test('kolom mata kuliah dosen lama diisi dari mata kuliah yang dipilih', function () {
    // Kolom teks `mata_kuliah` dipertahankan supaya data lama tidak hilang,
    // tapi isinya harus selalu mengikuti mata kuliah yang dipilih lewat form.
    $mataKuliah = MataKuliah::factory()->create(['nama_mata_kuliah' => 'Kimia Industri']);

    $dosen = Dosen::factory()->create(['mata_kuliah_id' => $mataKuliah->id]);

    expect($dosen->fresh()->mata_kuliah)->toBe('Kimia Industri')
        ->and($dosen->namaMataKuliah())->toBe('Kimia Industri');
});

/*
| Rekap per jadwal dan per tanggal memecah catatan yang sama dengan rekap per
| mata kuliah: satu sumber baris (`absensis` + join `sesi_absensis`/`jadwals`),
| dua cara membaca, yaitu per pertemuan jadwal dan per hari, supaya laporan
| presensi lebih jelas.
*/

test('laporan presensi menampilkan rekap per jadwal dan per tanggal', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    $mahasiswaHadir = mahasiswaDiKelas($kelas, 'Mahasiswa Hadir Jadwal');
    $mahasiswaAlpa = mahasiswaDiKelas($kelas, 'Mahasiswa Alpa Jadwal');

    $jadwal = Jadwal::factory()->create([
        'kelas_id' => $kelas->id,
        'dosen_id' => $dosen->id,
        'mata_kuliah' => 'Informatika',
        'hari' => 'Senin',
        'jam_mulai' => '07:00',
        'jam_selesai' => '08:30',
    ]);

    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'SESI-PER-JADWAL',
        'jadwal_id' => $jadwal->id,
        'waktu_mulai' => now()->subHour(),
        'waktu_selesai' => now(),
        'status' => 'aktif',
    ]);

    foreach ([[$mahasiswaHadir, 'hadir'], [$mahasiswaAlpa, 'alpha']] as [$mahasiswa, $status]) {
        Absensi::create([
            'mahasiswa_id' => $mahasiswa->id,
            'sesi_absensi_id' => $sesi->id,
            'waktu_absen' => now(),
            'status' => $status,
        ]);
    }

    $this->actingAs($dosen->user)
        ->get(route('dosen.laporan'))
        ->assertOk()
        // Kedua rekap kini satu panel dengan tab, bukan dua kartu terpisah.
        ->assertSee('Rekap per Jadwal & Tanggal')
        ->assertSee('data-rekapTab', false)
        ->assertSee('data-rekapPanel', false)
        ->assertSee('Rekap per Jadwal')
        ->assertSee('Rekap per Tanggal')
        ->assertSee('Senin, 07:00 - 08:30')
        ->assertSee('Informatika &middot; Kelas A', false)
        ->assertSee(now()->locale('id')->translatedFormat('l, d F Y'));

    $payload = $this->actingAs($dosen->user)
        ->getJson(route('dosen.laporan'))
        ->assertOk()
        ->json();

    expect($payload['perJadwal'][0]['jadwal'])->toBe('Senin, 07:00 - 08:30')
        ->and($payload['perJadwal'][0]['mata_kuliah'])->toBe('Informatika')
        ->and($payload['perJadwal'][0]['kelas'])->toBe('Kelas A')
        ->and($payload['perJadwal'][0]['sesi'])->toBe(1)
        ->and($payload['perJadwal'][0]['persentase'])->toBe(50)
        ->and($payload['perHari'][0]['sesi'])->toBe(1)
        ->and($payload['perHari'][0]['namaTanggal'])->toBe(now()->locale('id')->translatedFormat('l, d F Y'));
});

test('catatan tanpa jadwal masuk grup tanpa jadwal pada rekap per jadwal', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    $mahasiswa = mahasiswaDiKelas($kelas, 'Mahasiswa Riwayat Lama');

    // Sesi lama sebelum `jadwal_id` ada, persis seperti data production.
    $sesi = SesiAbsensi::create([
        'kode_sesi' => 'SESI-TANPA-JADWAL',
        'waktu_mulai' => now()->subHour(),
        'waktu_selesai' => now(),
        'status' => 'aktif',
    ]);

    Absensi::create([
        'mahasiswa_id' => $mahasiswa->id,
        'sesi_absensi_id' => $sesi->id,
        'waktu_absen' => now(),
        'status' => 'hadir',
    ]);

    $payload = $this->actingAs($dosen->user)
        ->getJson(route('dosen.laporan'))
        ->assertOk()
        ->json();

    expect($payload['perJadwal'])->toHaveCount(1)
        ->and($payload['perJadwal'][0]['jadwal'])->toBe(SesiAbsensi::TANPA_JADWAL)
        ->and($payload['perJadwal'][0]['mata_kuliah'])->toBeNull()
        ->and($payload['perJadwal'][0]['persentase'])->toBe(100);
});

test('tab progres presensi juga menampilkan rekap per jadwal dan per tanggal', function () {
    $dosen = dosenDenganAkun();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas A', 'status' => 'Aktif']);
    $kelas->dosen()->sync([$dosen->id]);

    mahasiswaDiKelas($kelas, 'Mahasiswa Progres Jadwal');

    $this->actingAs($dosen->user)
        ->get(route('dosen.laporan', ['tab' => 'periode']))
        ->assertOk()
        ->assertSee('Progres per Jadwal')
        ->assertSee('Progres per Tanggal');
});

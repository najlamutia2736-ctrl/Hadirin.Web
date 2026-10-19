<?php

use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/*
| Halaman kerja (CMS, dashboard dosen, absensi) hanya boleh dibuka setelah login
| berhasil, dan tiap role hanya boleh masuk ke arealanya sendiri. Test di file
| ini mengunci kedua aturan itu supaya tidak ada area yang terbuka lagi.
*/

uses(RefreshDatabase::class);

/**
 * Route CMS yang harus selalu tertutup untuk tamu.
 *
 * @return list<array{0:string,1:string}> [nama route, judul di halaman]
 */
function halamanCms(): array
{
    return [
        ['cms.dashboard', 'Dashboard'],
        ['cms.mahasiswa', 'Manajemen Mahasiswa'],
        ['cms.dosens', 'Manajemen Dosen'],
        ['cms.kelas', 'Manajemen Kelas'],
        ['cms.mata-kuliah', 'Manajemen Mata Kuliah'],
        ['cms.jadwal', 'Jadwal Mengajar'],
        ['cms.users', 'Manajemen Pengguna'],
        ['cms.rekap', 'Rekap Kehadiran'],
    ];
}

/**
 * Route dashboard dosen yang harus selalu tertutup untuk tamu.
 *
 * @return list<string>
 */
function halamanDosen(): array
{
    return ['dosen.dashboard', 'dosen.kelola', 'dosen.laporan'];
}

/*
| Tamu belum boleh masuk ke halaman apa pun milik ketiga role.
*/

test('tamu diarahkan ke halaman login saat membuka halaman cms', function (string $route) {
    $this->get(route($route))
        ->assertRedirect(route('login'));
})->with(halamanCms());

test('tamu diarahkan ke halaman login saat membuka dashboard dosen', function (string $route) {
    $this->get(route($route))
        ->assertRedirect(route('login'));
})->with(halamanDosen());

test('tamu tidak bisa mengirim request tulis ke halaman cms', function () {
    loginAdmin();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'Kelas Z']);

    // Logout dulu supaya benar-benar jadi tamu, lalu coba request tulis.
    $this->post(route('logout'));

    $this->post(route('cms.kelas.store'), [
        'name' => 'Kelas Gelap',
        'level' => 'X',
        'tahun_akademik' => now()->year,
    ])->assertRedirect(route('login'));

    $this->assertDatabaseMissing('kelas', ['nama_kelas' => 'Kelas Gelap']);
    expect($kelas->fresh())->not->toBeNull();
});

test('endpoint json dashboard dosen menolak tamu dengan 401', function () {
    $this->getJson(route('dosen.dashboard'))->assertUnauthorized();
    $this->postJson(route('dosen.kelola.mahasiswa.store'), [])->assertUnauthorized();
});

/*
| Sudah login, tapi role-nya harus cocok dengan area yang dibuka.
*/

test('mahasiswa yang sudah login tidak bisa membuka halaman cms', function (string $route) {
    $this->actingAs(User::factory()->create(['role' => 'Mahasiswa']))
        ->get(route($route))
        ->assertForbidden();
})->with(halamanCms());

test('mahasiswa yang sudah login tidak bisa membuka dashboard dosen', function (string $route) {
    $this->actingAs(User::factory()->create(['role' => 'Mahasiswa']))
        ->get(route($route))
        ->assertForbidden();
})->with(halamanDosen());

test('dosen yang sudah login tidak bisa membuka halaman cms', function (string $route) {
    loginDosen();

    $this->get(route($route))->assertForbidden();
})->with(halamanCms());

test('admin yang sudah login tidak bisa membuka dashboard dosen', function (string $route) {
    loginAdmin();

    $this->get(route($route))->assertForbidden();
})->with(halamanDosen());

test('admin dan operator tetap bisa membuka seluruh halaman cms', function (string $role) {
    loginAdmin($role);

    foreach (halamanCms() as [$route, $judul]) {
        $this->get(route($route))
            ->assertOk()
            ->assertSee($judul);
    }
})->with(['Admin', 'Operator']);

test('dosen tetap bisa membuka seluruh halaman dashboard dosen', function () {
    loginDosen();

    foreach (halamanDosen() as $route) {
        $this->get(route($route))->assertOk();
    }
});

/*
| Logout hanya boleh dipanggil oleh akun yang memang sedang login.
*/

test('halaman logout menolak tamu', function () {
    $this->post(route('logout'))->assertRedirect(route('login'));
});

test('logout mengembalikan akun ke status tamu', function () {
    loginAdmin();

    $this->post(route('logout'))->assertRedirect(route('login'));

    $this->assertGuest();
    $this->get(route('cms.dashboard'))->assertRedirect(route('login'));
});

/*
| Navbar tidak boleh menawarkan link yang hanya akan memantulkan tamu ke login.
*/

test('navbar tamu tidak menampilkan link dashboard', function () {
    // Yang dicek adalah label link di navbar, bukan URL-nya: kartu peran di
    // beranda memang memuat tautan ke dashboard dosen dan admin karena tugasnya
    // justru menawarkan pilihan peran. Yang tidak boleh bocor ke tamu adalah
    // menu navigasi, karena keduanya dilindungi middleware `role`.
    foreach (['beranda', 'login'] as $route) {
        $this->get(route($route))
            ->assertOk()
            ->assertSee(route('login'), false)
            ->assertDontSee('>Dashboard Dosen<', false)
            ->assertDontSee('>Dashboard Admin<', false)
            ->assertDontSee('>Rekap<', false);
    }
});

test('navbar admin tidak menampilkan link dashboard dosen', function () {
    loginAdmin();

    $this->get(route('cms.dashboard'))
        ->assertOk()
        ->assertSee(route('cms.dashboard'), false)
        ->assertDontSee(route('dosen.dashboard'), false);
});

test('navbar dosen tidak menampilkan link area cms', function () {
    loginDosen();

    $this->get(route('dosen.dashboard'))
        ->assertOk()
        ->assertSee(route('dosen.dashboard'), false)
        ->assertDontSee('>Dashboard Admin<', false)
        ->assertDontSee(route('cms.rekap'), false);
});

/*
| Absensi tetap milik mahasiswa. Mahasiswanya sendiri harus bisa masuk, akun lain tidak.
*/

test('mahasiswa yang punya profil tetap bisa membuka halaman absensi', function () {
    $user = User::factory()->create(['role' => 'Mahasiswa', 'status' => 'Aktif']);
    Mahasiswa::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('absensi.index'))
        ->assertOk();
});

/*
| Halaman login dan halaman publik lain tidak boleh menampilkan nama akun
| maupun tombol Logout untuk pengunjung yang belum masuk. Kalau iya, orang
| mengira sudah login padahal sebenarnya belum.
*/

test('halaman publik tidak menampilkan nama akun maupun tombol logout untuk tamu', function (string $route) {
    $this->get(route($route))
        ->assertOk()
        ->assertSee(route('login'), false)
        ->assertDontSee('Logout')
        ->assertDontSee('fa-user-circle', false);
    // Route `home` tidak ikut karena `/beranda` lama sekarang hanya
    // pengalihan, bukan halaman yang dirender.
})->with(['login', 'beranda']);

test('halaman login tidak menampilkan nama akun yang sedang login', function () {
    $user = User::factory()->create(['name' => 'Martha Arinda S.Pd', 'status' => 'Aktif']);

    // Sesi masih hidup, jadi middleware `guest` yang mengarahkan ke beranda.
    $this->actingAs($user)
        ->get(route('login'))
        ->assertRedirect(route('beranda'));

    // Setelah keluar, halaman login bersih: tidak ada nama, tidak ada Logout.
    $this->post(route('logout'));

    $this->assertGuest();

    $this->get(route('login'))
        ->assertOk()
        ->assertDontSee('Martha Arinda S.Pd')
        ->assertDontSee('Logout');
});

test('navbar menampilkan nama akun dan logout hanya setelah benar-benar login', function () {
    $user = User::factory()->create(['name' => 'Martha Arinda S.Pd', 'status' => 'Aktif']);

    $this->actingAs($user)
        ->get(route('beranda'))
        ->assertOk()
        ->assertSee('Martha Arinda S.Pd')
        ->assertSee('Logout');
});

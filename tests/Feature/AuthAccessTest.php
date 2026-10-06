<?php

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/*
| Halaman kerja (CMS, dashboard guru, absensi) hanya boleh dibuka setelah login
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
        ['cms.student', 'Manajemen Siswa'],
        ['cms.teachers', 'Manajemen Guru'],
        ['cms.classes', 'Manajemen Kelas'],
        ['cms.mata-pelajaran', 'Manajemen Mata Pelajaran'],
        ['cms.jadwal', 'Jadwal Mengajar'],
        ['cms.users', 'Manajemen Pengguna'],
        ['cms.rekap', 'Rekap Kehadiran'],
    ];
}

/**
 * Route dashboard guru yang harus selalu tertutup untuk tamu.
 *
 * @return list<string>
 */
function halamanGuru(): array
{
    return ['guru.dashboard', 'guru.progres', 'guru.kelola', 'guru.laporan', 'guru.realtime'];
}

/*
| Tamu belum boleh masuk ke halaman apa pun milik ketiga role.
*/

test('tamu diarahkan ke halaman login saat membuka halaman cms', function (string $route) {
    $this->get(route($route))
        ->assertRedirect(route('login'));
})->with(halamanCms());

test('tamu diarahkan ke halaman login saat membuka dashboard guru', function (string $route) {
    $this->get(route($route))
        ->assertRedirect(route('login'));
})->with(halamanGuru());

test('tamu tidak bisa mengirim request tulis ke halaman cms', function () {
    loginAdmin();
    $kelas = Kelas::factory()->create(['nama_kelas' => 'X.9']);

    // Logout dulu supaya benar-benar jadi tamu, lalu coba request tulis.
    $this->post(route('logout'));

    $this->post(route('cms.classes.store'), [
        'name' => 'Kelas Gelap',
        'level' => 'X',
        'tahun_ajaran' => now()->year,
    ])->assertRedirect(route('login'));

    $this->assertDatabaseMissing('kelas', ['nama_kelas' => 'Kelas Gelap']);
    expect($kelas->fresh())->not->toBeNull();
});

test('endpoint json dashboard guru menolak tamu dengan 401', function () {
    $this->getJson(route('guru.dashboard'))->assertUnauthorized();
    $this->postJson(route('guru.kelola.siswa.store'), [])->assertUnauthorized();
});

/*
| Sudah login, tapi role-nya harus cocok dengan area yang dibuka.
*/

test('siswa yang sudah login tidak bisa membuka halaman cms', function (string $route) {
    $this->actingAs(User::factory()->create(['role' => 'Siswa']))
        ->get(route($route))
        ->assertForbidden();
})->with(halamanCms());

test('siswa yang sudah login tidak bisa membuka dashboard guru', function (string $route) {
    $this->actingAs(User::factory()->create(['role' => 'Siswa']))
        ->get(route($route))
        ->assertForbidden();
})->with(halamanGuru());

test('guru yang sudah login tidak bisa membuka halaman cms', function (string $route) {
    loginGuru();

    $this->get(route($route))->assertForbidden();
})->with(halamanCms());

test('admin yang sudah login tidak bisa membuka dashboard guru', function (string $route) {
    loginAdmin();

    $this->get(route($route))->assertForbidden();
})->with(halamanGuru());

test('admin dan operator tetap bisa membuka seluruh halaman cms', function (string $role) {
    loginAdmin($role);

    foreach (halamanCms() as [$route, $judul]) {
        $this->get(route($route))
            ->assertOk()
            ->assertSee($judul);
    }
})->with(['Admin', 'Operator']);

test('guru tetap bisa membuka seluruh halaman dashboard guru', function () {
    loginGuru();

    foreach (halamanGuru() as $route) {
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

test('navbar tamu hanya menawarkan halaman publik dan tombol login', function () {
    // `/home` dan `/login` memakai `navbar.blade.php` tanpa kartu peran, jadi
    // di situ tidak boleh ada satu pun link ke area CMS maupun guru.
    foreach (['home', 'login'] as $route) {
        $this->get(route($route))
            ->assertOk()
            ->assertSee(route('login'), false)
            ->assertDontSee(route('guru.dashboard'), false)
            ->assertDontSee('>Dashboard Admin<', false)
            ->assertDontSee('>Rekap<', false);
    }
});

test('navbar admin tidak menampilkan link dashboard guru', function () {
    loginAdmin();

    $this->get(route('cms.dashboard'))
        ->assertOk()
        ->assertSee(route('cms.dashboard'), false)
        ->assertDontSee(route('guru.dashboard'), false);
});

test('navbar guru tidak menampilkan link area cms', function () {
    loginGuru();

    $this->get(route('guru.dashboard'))
        ->assertOk()
        ->assertSee(route('guru.dashboard'), false)
        ->assertDontSee('>Dashboard Admin<', false)
        ->assertDontSee(route('cms.rekap'), false);
});

/*
| Absensi tetap milik siswa. Siswanya sendiri harus bisa masuk, akun lain tidak.
*/

test('siswa yang punya profil tetap bisa membuka halaman absensi', function () {
    $user = User::factory()->create(['role' => 'Siswa', 'status' => 'Aktif']);
    Siswa::factory()->create(['user_id' => $user->id]);

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
})->with(['home', 'login', 'beranda']);

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

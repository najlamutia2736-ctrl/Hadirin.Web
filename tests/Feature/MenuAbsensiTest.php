<?php

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Halaman yang memuat link "Absen Siswa" di navigasi publik.
 */
function halamanDenganMenuAbsen(): array
{
    return ['/login', '/'];
}

/**
 * Halaman yang masih bisa dibuka pengguna yang sudah login.
 *
 * `/login` tidak ikut di sini karena middleware `guest` mengalihkan pengguna
 * yang sudah login ke halaman depannya.
 */
function halamanYangTerbukaSaatLogin(): array
{
    return ['/'];
}

test('link absen siswa disembunyikan saat belum login', function () {
    foreach (halamanDenganMenuAbsen() as $url) {
        $this->get($url)
            ->assertOk()
            ->assertDontSee(route('absensi.index'), false);
    }
});

test('link absen siswa disembunyikan untuk akun admin dan guru', function () {
    foreach (halamanYangTerbukaSaatLogin() as $url) {
        foreach (['Admin', 'Guru'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->get($url)
                ->assertOk()
                ->assertDontSee(route('absensi.index'), false);
        }
    }
});

test('link absen siswa muncul untuk akun siswa yang punya profil', function () {
    $user = User::factory()->create(['role' => 'Siswa']);

    Siswa::factory()->create(['user_id' => $user->id]);

    foreach (halamanYangTerbukaSaatLogin() as $url) {
        $this->actingAs($user->fresh())
            ->get($url)
            ->assertOk()
            ->assertSee(route('absensi.index'), false)
            ->assertSee('Absen Siswa');
    }
});

test('link absen siswa tetap disembunyikan untuk akun siswa tanpa profil', function () {
    // Role-nya Siswa, tapi tidak punya baris di tabel `siswas`, jadi halaman
    // /absensi akan menolak dengan 403. Menunya pun tidak boleh tampil.
    $user = User::factory()->create(['role' => 'Siswa']);

    expect($user->siswa)->toBeNull();

    foreach (halamanYangTerbukaSaatLogin() as $url) {
        $this->actingAs($user)
            ->get($url)
            ->assertOk()
            ->assertDontSee(route('absensi.index'), false);
    }
});

test('membuka halaman lewat alamat lama beranda tidak mengakhiri sesi', function () {
    // `/beranda` lama hanya pengalihan ke `/`. Sesi harus tetap hidup supaya
    // akun siswa yang punya profil tidak kehilangan sesinya.
    $user = User::factory()->create(['role' => 'Siswa']);

    Siswa::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->get('/beranda')->assertRedirect(route('beranda'));

    $this->assertAuthenticatedAs($user);

    $this->get(route('beranda'))
        ->assertOk()
        ->assertSee(route('absensi.index'), false)
        ->assertSee('Absen Siswa');
});

/*
| Kartu peran "Siswa" di beranda. Berbeda dengan menu "Absen Siswa", kartu
| ini tidak pernah disembunyikan: tugasnya justru menawarkan pilihan peran,
| jadi orang yang belum login adalah target utamanya. Yang dijaga hanya
| tujuan kliknya supaya URL halaman absensi tidak bocor ke akun lain.
*/

test('kartu peran siswa selalu tampil di beranda untuk semua pengunjung', function () {
    // Tamu, siswa tanpa profil, guru, dan admin semuanya tetap melihat
    // ketiga kartu peran supaya bisa memilih masuk sebagai siswa.
    $this->get('/')
        ->assertOk()
        ->assertSee('Siswa')
        ->assertSee('Guru / Wali Kelas')
        ->assertSee('Admin / Kepsek')
        // Teks tombol kartu sudah diubah menjadi "Klik" untuk ketiga peran,
        // jadi yang dicek adalah isi kartu Siswanya sendiri, bukan label tombol.
        ->assertSee('Absen mandiri lewat scan QRCode atau kode NISN.');

    foreach (['Admin', 'Guru'] as $role) {
        $this->actingAs(User::factory()->create(['role' => $role]))
            ->get('/')
            ->assertOk()
            ->assertSee('Siswa')
            ->assertSee('Bukan Akun Siswa');
    }

    $tanpaProfil = User::factory()->create(['role' => 'Siswa']);

    $this->actingAs($tanpaProfil)
        ->get('/')
        ->assertOk()
        ->assertSee('Siswa')
        ->assertSee('Bukan Akun Siswa');
});

test('kartu peran siswa untuk tamu mengarah ke login, bukan ke halaman absensi', function () {
    // Middleware auth yang menjaga /absensi, jadi kartu cukup mengarahkan ke
    // login dan tidak boleh memuat URL absensi sama sekali.
    $this->get('/')
        ->assertOk()
        ->assertSee(route('login'), false)
        ->assertDontSee(route('absensi.index'), false);
});

test('kartu peran siswa untuk siswa yang punya profil langsung ke halaman absensi', function () {
    $user = User::factory()->create(['role' => 'Siswa']);

    Siswa::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user->fresh())
        ->get('/')
        ->assertOk()
        ->assertSee(route('absensi.index'), false)
        ->assertSee('Absen Sekarang');
});

/*
| Panduan cara absen di halaman depan. Isinya boleh tampil untuk semua orang,
| karena cuma penjelasan cara absen — bukan halaman absennya. Yang tetap
| dijaga: halaman depan tidak boleh memuat URL halaman absensi ke selain
| akun siswa yang benar-benar punya profil.
*/

test('panduan cara absen tampil di halaman depan untuk semua pengunjung', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Panduan Cara Absen')
        ->assertSee('Scan QR Code')
        ->assertSee('Kode NISN')
        ->assertSee('Izin / Sakit')
        ->assertSee('Notifikasi');
});

test('panduan cara absen di halaman depan tidak membocorkan url halaman absensi', function () {
    // Halaman depan terbuka untuk semua orang, termasuk guru dan admin yang
    // tidak punya hak ke /absensi. Setiap kartu panduan karena itu berhenti
    // pada penjelasannya, tanpa link ke halaman absen.
    foreach ([null, 'Admin', 'Guru'] as $role) {
        $request = $role === null
            ? $this->get('/')
            : $this->actingAs(User::factory()->create(['role' => $role]))->get('/');

        $request->assertOk();

        foreach (config('metode-absensi') as $metode) {
            $request->assertDontSee(route($metode['route']), false);
        }
    }
});

test('tombol di bawah panduan mengarah sesuai sesi', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Login untuk Mulai Absen')
        ->assertSee(route('login'), false);

    // Sudah login tapi bukan siswa: tombolnya mati, bukan mengarah ke /absensi
    // yang pasti menolak dengan 403.
    $this->actingAs(User::factory()->create(['role' => 'Admin']))
        ->get('/')
        ->assertOk()
        ->assertSee('Gunakan akun siswa untuk absen');

    $user = User::factory()->create(['role' => 'Siswa']);

    Siswa::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user->fresh())
        ->get('/')
        ->assertOk()
        ->assertSee('Mulai Absen Sekarang')
        ->assertSee(route('absensi.index'), false);
});

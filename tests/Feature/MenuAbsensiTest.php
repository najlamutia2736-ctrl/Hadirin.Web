<?php

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Halaman publik yang boleh memuat tautan ke halaman absensi.
 *
 * Navbar sudah tidak menampilkan link apa pun, jadi yang diuji di sini adalah
 * kartu peran "Mahasiswa" di beranda.
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

test('link absen mahasiswa disembunyikan saat belum login', function () {
    foreach (halamanDenganMenuAbsen() as $url) {
        $this->get($url)
            ->assertOk()
            ->assertDontSee(route('absensi.index'), false);
    }
});

test('link absen mahasiswa disembunyikan untuk akun admin dan dosen', function () {
    foreach (halamanYangTerbukaSaatLogin() as $url) {
        foreach (['Admin', 'Dosen'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->get($url)
                ->assertOk()
                ->assertDontSee(route('absensi.index'), false);
        }
    }
});

test('mahasiswa yang punya profil bisa masuk ke halaman absensi dari beranda', function () {
    $user = User::factory()->create(['role' => 'Mahasiswa']);

    Mahasiswa::factory()->create(['user_id' => $user->id]);

    foreach (halamanYangTerbukaSaatLogin() as $url) {
        // Link ke absensi tidak lagi ada di navbar, tapi kartu peran "Mahasiswa"
        // di beranda tetap menunjuk ke halaman absensi.
        $this->actingAs($user->fresh())
            ->get($url)
            ->assertOk()
            ->assertSee(route('absensi.index'), false)
            ->assertSee('Presen Sekarang');
    }
});

test('link absen mahasiswa tetap disembunyikan untuk akun mahasiswa tanpa profil', function () {
    // Role-nya Mahasiswa, tapi tidak punya baris di tabel `mahasiswa`, jadi halaman
    // /absensi akan menolak dengan 403. Menunya pun tidak boleh tampil.
    $user = User::factory()->create(['role' => 'Mahasiswa']);

    expect($user->mahasiswa)->toBeNull();

    foreach (halamanYangTerbukaSaatLogin() as $url) {
        $this->actingAs($user)
            ->get($url)
            ->assertOk()
            ->assertDontSee(route('absensi.index'), false);
    }
});

test('membuka halaman lewat alamat lama beranda tidak mengakhiri sesi', function () {
    // `/beranda` lama hanya pengalihan ke `/`. Sesi harus tetap hidup supaya
    // akun mahasiswa yang punya profil tidak kehilangan sesinya.
    $user = User::factory()->create(['role' => 'Mahasiswa']);

    Mahasiswa::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)->get('/beranda')->assertRedirect(route('beranda'));

    $this->assertAuthenticatedAs($user);

    $this->get(route('beranda'))
        ->assertOk()
        ->assertSee(route('absensi.index'), false)
        ->assertSee('Presen Sekarang');
});

/*
| Kartu peran "Mahasiswa" di beranda. Kartu ini tidak pernah disembunyikan:
| tugasnya justru menawarkan pilihan peran, jadi orang yang belum login adalah
| target utamanya. Yang dijaga hanya tujuan kliknya supaya URL halaman absensi
| tidak bocor ke akun lain.
*/

test('kartu peran mahasiswa selalu tampil di beranda untuk semua pengunjung', function () {
    // Tamu, mahasiswa tanpa profil, dosen, dan admin semuanya tetap melihat
    // ketiga kartu peran supaya bisa memilih masuk sebagai mahasiswa.
    $this->get('/')
        ->assertOk()
        ->assertSee('Mahasiswa')
        ->assertSee('Dosen / Pembimbing Akademik')
        ->assertSee('Admin / Kepsek')
        // Teks tombol kartu sudah diubah menjadi "Klik" untuk ketiga peran,
        // jadi yang dicek adalah isi kartu Mahasiswanya sendiri, bukan label tombol.
        ->assertSee('Presen mandiri lewat scan QRCode atau kode NIM.');

    foreach (['Admin', 'Dosen'] as $role) {
        $this->actingAs(User::factory()->create(['role' => $role]))
            ->get('/')
            ->assertOk()
            ->assertSee('Mahasiswa')
            ->assertSee('Bukan Akun Mahasiswa');
    }

    $tanpaProfil = User::factory()->create(['role' => 'Mahasiswa']);

    $this->actingAs($tanpaProfil)
        ->get('/')
        ->assertOk()
        ->assertSee('Mahasiswa')
        ->assertSee('Bukan Akun Mahasiswa');
});

test('kartu peran mahasiswa untuk tamu mengarah ke login, bukan ke halaman absensi', function () {
    // Middleware auth yang menjaga /absensi, jadi kartu cukup mengarahkan ke
    // login dan tidak boleh memuat URL absensi sama sekali.
    $this->get('/')
        ->assertOk()
        ->assertSee(route('login'), false)
        ->assertDontSee(route('absensi.index'), false);
});

test('kartu peran mahasiswa untuk mahasiswa yang punya profil langsung ke halaman absensi', function () {
    $user = User::factory()->create(['role' => 'Mahasiswa']);

    Mahasiswa::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user->fresh())
        ->get('/')
        ->assertOk()
        ->assertSee(route('absensi.index'), false)
        ->assertSee('Presen Sekarang');
});

/*
| Panduan cara absen di halaman depan. Isinya boleh tampil untuk semua orang,
| karena cuma penjelasan cara absen — bukan halaman absennya. Yang tetap
| dijaga: halaman depan tidak boleh memuat URL halaman absensi ke selain
| akun mahasiswa yang benar-benar punya profil.
*/

test('panduan cara absen tampil di halaman depan untuk semua pengunjung', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Panduan Cara Presen')
        ->assertSee('Scan QR Code')
        ->assertSee('Kode NIM')
        ->assertSee('Izin / Sakit')
        ->assertSee('Notifikasi');
});

test('panduan cara absen di halaman depan tidak membocorkan url halaman absensi', function () {
    // Halaman depan terbuka untuk semua orang, termasuk dosen dan admin yang
    // tidak punya hak ke /absensi. Setiap kartu panduan karena itu berhenti
    // pada penjelasannya, tanpa link ke halaman absen.
    foreach ([null, 'Admin', 'Dosen'] as $role) {
        $request = $role === null
            ? $this->get('/')
            : $this->actingAs(User::factory()->create(['role' => $role]))->get('/');

        $request->assertOk();

        foreach (config('metode-absensi') as $metode) {
            $request->assertDontSee(route($metode['route']), false);
        }
    }
});

test('tidak ada tombol aksi di bawah panduan cara absen', function () {
    // Panduan murni penjelasan; navigasi ke absensi hanya lewat navbar.
    $mahasiswa = User::factory()->create(['role' => 'Mahasiswa']);

    Mahasiswa::factory()->create(['user_id' => $mahasiswa->id]);

    $halaman = [
        'tamu' => fn () => $this->get('/'),
        'admin' => fn () => $this->actingAs(User::factory()->create(['role' => 'Admin']))->get('/'),
        'mahasiswa' => fn () => $this->actingAs($mahasiswa->fresh())->get('/'),
    ];

    foreach ($halaman as $nama => $request) {
        $response = $request();

        $response->assertOk();
        $response->assertDontSee('Login untuk Mulai Presen');
        $response->assertDontSee('Mulai Presen Sekarang');
        // Needle persis tombol yang dihapus. Frasa polosnya masih dipakai sebagai
        // `title` pada kartu peran "Mahasiswa" di bagian Pilihlah Peranmu.
        $response->assertDontSee('<i class="fas fa-lock"></i> Gunakan akun mahasiswa untuk presen', false);
    }
});

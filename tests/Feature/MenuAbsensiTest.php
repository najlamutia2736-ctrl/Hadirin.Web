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
    return ['/', '/login', '/beranda'];
}

test('link absen siswa disembunyikan saat belum login', function () {
    foreach (halamanDenganMenuAbsen() as $url) {
        $this->get($url)
            ->assertOk()
            ->assertDontSee(route('absensi.index'), false);
    }
});

test('link absen siswa disembunyikan untuk akun admin dan guru', function () {
    foreach (halamanDenganMenuAbsen() as $url) {
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

    foreach (halamanDenganMenuAbsen() as $url) {
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

    foreach (halamanDenganMenuAbsen() as $url) {
        $this->actingAs($user)
            ->get($url)
            ->assertOk()
            ->assertDontSee(route('absensi.index'), false);
    }
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
    $this->get('/beranda')
        ->assertOk()
        ->assertSee('Siswa')
        ->assertSee('Guru / Wali Kelas')
        ->assertSee('Admin / Kepsek')
        // Teks tombol kartu sudah diubah menjadi "Klik" untuk ketiga peran,
        // jadi yang dicek adalah isi kartu Siswanya sendiri, bukan label tombol.
        ->assertSee('Absen mandiri lewat scan QRCode atau kode unik.');

    foreach (['Admin', 'Guru'] as $role) {
        $this->actingAs(User::factory()->create(['role' => $role]))
            ->get('/beranda')
            ->assertOk()
            ->assertSee('Siswa')
            ->assertSee('Bukan Akun Siswa');
    }

    $tanpaProfil = User::factory()->create(['role' => 'Siswa']);

    $this->actingAs($tanpaProfil)
        ->get('/beranda')
        ->assertOk()
        ->assertSee('Siswa')
        ->assertSee('Bukan Akun Siswa');
});

test('kartu peran siswa untuk tamu mengarah ke login, bukan ke halaman absensi', function () {
    // Middleware auth yang menjaga /absensi, jadi kartu cukup mengarahkan ke
    // login dan tidak boleh memuat URL absensi sama sekali.
    $this->get('/beranda')
        ->assertOk()
        ->assertSee(route('login'), false)
        ->assertDontSee(route('absensi.index'), false);
});

test('kartu peran siswa untuk siswa yang punya profil langsung ke halaman absensi', function () {
    $user = User::factory()->create(['role' => 'Siswa']);

    Siswa::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user->fresh())
        ->get('/beranda')
        ->assertOk()
        ->assertSee(route('absensi.index'), false)
        ->assertSee('Absen Sekarang');
});

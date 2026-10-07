<?php

use App\Models\Guru;
use App\Models\MataPelajaran;
use App\Models\User;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
 // ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/*
|--------------------------------------------------------------------------
| Helper Login
|--------------------------------------------------------------------------
|
| Halaman CMS dan dashboard guru dilindungi middleware `auth` + `role`, jadi
| test yang memanggil route-nya wajib login lebih dulu. Dua helper ini
| shortening `actingAs()` supaya setiap test cukup menulis `$this->loginAdmin()`.
|
*/

/**
 * Masuk sebagai akun admin (atau operator bila peran itu diminta).
 *
 * Mengembalikan model user-nya supaya test bisa memakai `$user->id` dan
 * sekaligus, tanpa perlu menulis `$this->actingAs()` lagi.
 */
function loginAdmin(string $role = 'Admin'): User
{
    $user = User::factory()->create(['role' => $role, 'status' => 'Aktif']);

    test()->actingAs($user);

    return $user;
}

/**
 * Masuk sebagai akun guru yang juga punya baris di tabel `gurus`.
 */
function loginGuru(string $nama = 'Guru Uji'): User
{
    $user = User::factory()->create([
        'name' => $nama,
        'role' => 'Guru',
        'status' => 'Aktif',
    ]);

    Guru::factory()->create(['user_id' => $user->id]);

    test()->actingAs($user);

    return $user;
}

/**
 * Mata pelajaran dari daftar bawaan migration, atau dibuat kalau belum ada.
 *
 * `DKV` dan `RPL` sudah diisi migration `add_jurusan_id_to_kelas_table`, jadi
 * test yang memakai kode tersebut tidak boleh memanggil factory (kode dan nama
 * mapel unique) tanpa mengecek dulu.
 */
function mapelDenganKode(string $kode, string $nama): MataPelajaran
{
    return MataPelajaran::query()->firstOrCreate(
        ['kode_mata_pelajaran' => $kode],
        ['nama_mata_pelajaran' => $nama],
    );
}

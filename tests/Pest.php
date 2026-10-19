<?php

use App\Models\Dosen;
use App\Models\MataKuliah;
use App\Models\User;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different kelas or traits.
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
| Halaman CMS dan dashboard dosen dilindungi middleware `auth` + `role`, jadi
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
 * Masuk sebagai akun dosen yang juga punya baris di tabel `dosens`.
 */
function loginDosen(string $nama = 'Dosen Uji'): User
{
    $user = User::factory()->create([
        'name' => $nama,
        'role' => 'Dosen',
        'status' => 'Aktif',
    ]);

    Dosen::factory()->create(['user_id' => $user->id]);

    test()->actingAs($user);

    return $user;
}

/**
 * Mata pelajaran dari daftar bawaan migration, atau dibuat kalau belum ada.
 *
 * `DKV` dan `RPL` sudah diisi migration `add_jurusan_id_to_kelas_table`, jadi
 * test yang memakai kode tersebut tidak boleh memanggil factory (kode dan nama
 * mata kuliah unique) tanpa mengecek dulu.
 */
function mataKuliahDenganKode(string $kode, string $nama): MataKuliah
{
    return MataKuliah::query()->firstOrCreate(
        ['kode_mata_kuliah' => $kode],
        ['nama_mata_kuliah' => $nama],
    );
}

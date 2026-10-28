<?php

use App\Models\Dosen;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\User;
use Database\Seeders\AkunSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('seeder membuat akun admin, dosen, dan mahasiswa lengkap dengan profilnya', function () {
    $this->seed(AkunSeeder::class);

    $admin = User::query()->where('email', 'admin@kampus.ac.id')->firstOrFail();
    $dosen = User::query()->where('email', '1987123456@dosen.kampus.ac.id')->firstOrFail();
    $mahasiswa = User::query()->where('email', '20260001@mahasiswa.kampus.ac.id')->firstOrFail();

    expect($admin->role)->toBe('Admin')
        ->and($admin->status)->toBe('Aktif')
        ->and(Hash::check('password123', $admin->password))->toBeTrue()
        ->and($dosen->role)->toBe('Dosen')
        ->and(Hash::check('1987123456', $dosen->password))->toBeTrue()
        ->and($mahasiswa->role)->toBe('Mahasiswa')
        ->and(Hash::check('20260001', $mahasiswa->password))->toBeTrue();

    // Profil wajib ada: dashboard dosen dan halaman absensi menolak akun
    // yang tidak punya baris di tabel `dosens` / `mahasiswa`.
    expect(Dosen::query()->where('nidn', '1987123456')->where('user_id', $dosen->id)->exists())->toBeTrue()
        ->and(Mahasiswa::query()->where('nim', '20260001')->where('user_id', $mahasiswa->id)->exists())->toBeTrue()
        ->and(Mahasiswa::query()->where('nim', '20260001')->value('kelas'))->toBe(Kelas::KELAS_TERSEDIA[0]);
});

test('seeder aman dijalankan berulang kali tanpa membuat akun ganda', function () {
    $this->seed(AkunSeeder::class);
    $this->seed(AkunSeeder::class);

    expect(User::query()->count())->toBe(3)
        ->and(Dosen::query()->count())->toBe(1)
        ->and(Mahasiswa::query()->count())->toBe(1)
        ->and(Kelas::query()->where('nama_kelas', Kelas::KELAS_TERSEDIA[0])->exists())->toBeTrue();
});

test('akun hasil seeder bisa dipakai login lewat form login', function () {
    $this->seed(AkunSeeder::class);

    $this->from(route('login'))
        ->post(route('login.store'), [
            'email' => '20260001@mahasiswa.kampus.ac.id',
            'password' => '20260001',
        ])
        ->assertRedirect(route('absensi.index'));

    expect(auth()->user()?->role)->toBe('Mahasiswa');
});

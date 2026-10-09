<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\SesiAbsensiService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Sesi absensi hari ikut dibuat di sini supaya instalasi baru langsung bisa
     * dipakai absen tanpa menunggu scheduler berjalan. Pada server sebenarnya
     * sesi dibuat otomatis oleh command `sesi:absensi` setiap hari.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        app(SesiAbsensiService::class)->buatSesiHarian(today());
    }
}

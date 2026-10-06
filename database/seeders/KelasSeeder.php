<?php

namespace Database\Seeders;

use App\Models\Kelas;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KelasSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Semua rombel dibuat dari daftar baku supaya isinya sama persis dengan
        // yang dipakai form siswa dan halaman Classes.
        foreach (Kelas::ROMBEL_TERSEDIA as $namaKelas) {
            if (! Kelas::query()->where('nama_kelas', $namaKelas)->exists()) {
                Kelas::factory()->create(['nama_kelas' => $namaKelas]);
            }
        }
    }
}

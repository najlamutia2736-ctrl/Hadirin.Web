<?php

namespace Database\Factories;

use App\Models\Kelas;
use App\Models\MataPelajaran;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kelas>
 */
class KelasFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $namaKelas = fake()->unique()->randomElement(Kelas::ROMBEL_TERSEDIA);

        return [
            'nama_kelas' => $namaKelas,
            // Nama kelas berformat "XII.1", jadi tingkat diambil dari bagian
            // sebelum titik, bukan dari pemisah tanda hubung.
            'tingkat' => explode('.', $namaKelas)[0],
            'mata_pelajaran_id' => MataPelajaran::factory(),
            'wali_kelas_id' => null,
            'ruang' => 'R. '.fake()->numberBetween(101, 999),
            'tahun_ajaran' => now()->year,
            'status' => 'Aktif',
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Jurusan;
use App\Models\Kelas;
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
            'tingkat' => explode('-', $namaKelas)[0],
            'jurusan_id' => Jurusan::factory(),
            'wali_kelas_id' => null,
            'ruang' => 'R. '.fake()->numberBetween(101, 999),
            'tahun_ajaran' => now()->year,
            'status' => 'Aktif',
        ];
    }
}

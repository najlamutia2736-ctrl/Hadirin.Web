<?php

namespace Database\Factories;

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
        return [
            'nama_kelas' => fake()->unique()->bothify('??-??'),
            'tingkat' => fake()->randomElement(['X', 'XI', 'XII']),
            'wali_kelas_id' => null,
            'ruang' => 'R. '.fake()->numberBetween(101, 999),
            'tahun_ajaran' => now()->year,
            'status' => 'Aktif',
        ];
    }
}

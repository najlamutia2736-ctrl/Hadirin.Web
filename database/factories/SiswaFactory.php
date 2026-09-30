<?php

namespace Database\Factories;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Siswa>
 */
class SiswaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nisn' => fake()->unique()->numerify('########'),
            'jenis_kelamin' => fake()->randomElement(['L', 'P']),
            'wali' => fake()->name(),
            'telepon_wali' => fake()->numerify('08##########'),
            'kelas' => fake()->randomElement(Kelas::ROMBEL_TERSEDIA),
            'jurusan' => null,
            'status' => 'Aktif',
        ];
    }
}

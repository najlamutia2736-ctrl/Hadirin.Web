<?php

namespace Database\Factories;

use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mahasiswa>
 */
class MahasiswaFactory extends Factory
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
            'nim' => fake()->unique()->numerify('########'),
            'jenis_kelamin' => fake()->randomElement(['L', 'P']),
            'wali' => fake()->name(),
            'telepon_wali' => fake()->numerify('08##########'),
            'kelas' => fake()->randomElement(Kelas::KELAS_TERSEDIA),
            'status' => 'Aktif',
        ];
    }
}

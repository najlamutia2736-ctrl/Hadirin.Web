<?php

namespace Database\Factories;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Guru>
 */
class GuruFactory extends Factory
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
            'nip' => fake()->unique()->numerify('198##########'),
            'mata_pelajaran' => fake()->randomElement(['Matematika', 'Bahasa Indonesia', 'Informatika', 'IPA', 'Sejarah']),
            'telepon' => fake()->numerify('08##########'),
            'status' => 'Aktif',
        ];
    }
}

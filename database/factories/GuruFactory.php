<?php

namespace Database\Factories;

use App\Models\Guru;
use App\Models\MataPelajaran;
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
     * Mata pelajaran diambil dari tabel `mata_pelajaran` supaya guru yang
     * dibuat factory langsung terhubung ke mapel sungguhan, sama seperti
     * yang dilakukan form guru di dashboard admin.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nip' => fake()->unique()->numerify('198##########'),
            'mata_pelajaran_id' => MataPelajaran::factory(),
            'telepon' => fake()->numerify('08##########'),
            'status' => 'Aktif',
        ];
    }
}

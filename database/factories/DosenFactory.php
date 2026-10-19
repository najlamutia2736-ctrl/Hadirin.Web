<?php

namespace Database\Factories;

use App\Models\Dosen;
use App\Models\MataKuliah;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dosen>
 */
class DosenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Mata pelajaran diambil dari tabel `mata_kuliah` supaya dosen yang
     * dibuat factory langsung terhubung ke mata kuliah sungguhan, sama seperti
     * yang dilakukan form dosen di dashboard admin.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nidn' => fake()->unique()->numerify('198##########'),
            'mata_kuliah_id' => MataKuliah::factory(),
            'telepon' => fake()->numerify('08##########'),
            'status' => 'Aktif',
        ];
    }
}

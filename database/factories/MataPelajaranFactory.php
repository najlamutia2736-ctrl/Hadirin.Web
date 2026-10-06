<?php

namespace Database\Factories;

use App\Models\MataPelajaran;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MataPelajaran>
 */
class MataPelajaranFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Kode memakai awalan `MAPEL` supaya tidak pernah bentrok dengan daftar
     * mata pelajaran bawaan yang diisi migration
     * `add_jurusan_id_to_kelas_table`.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_mata_pelajaran' => Str::upper(fake()->unique()->lexify('MAPEL???')),
            'nama_mata_pelajaran' => fake()->randomElement([
                'Matematika',
                'Bahasa Indonesia',
                'Bahasa Inggris',
                'Fisika',
                'Kimia',
                'Biologi',
                'Sejarah',
                'Informatika',
                'Pendidikan Pancasila',
            ]),
        ];
    }
}

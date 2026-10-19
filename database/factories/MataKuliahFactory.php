<?php

namespace Database\Factories;

use App\Models\MataKuliah;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MataKuliah>
 */
class MataKuliahFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Kode memakai awalan `MK` supaya tidak pernah bentrok dengan daftar
     * mata kuliah bawaan yang diisi migration
     * `add_jurusan_id_to_kelas_table`.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_mata_kuliah' => Str::upper(fake()->unique()->lexify('MK???')),
            'nama_mata_kuliah' => fake()->randomElement([
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

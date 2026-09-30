<?php

namespace Database\Factories;

use App\Models\Jurusan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Jurusan>
 */
class JurusanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Kode memakai awalan `JUR` supaya tidak pernah bentrok dengan daftar
     * jurusan bawaan yang diisi migration `add_jurusan_id_to_kelas_table`.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode_jurusan' => Str::upper(fake()->unique()->lexify('JUR???')),
            'nama_jurusan' => fake()->randomElement([
                'Rekayasa Perangkat Lunak',
                'Farmasi',
                'Desain Komunikasi Visual',
                'Teknik Jaringan Komputer dan Telekomunikasi',
                'Kimia Industri',
                'Tata Busana',
                'Rekayasa Industri',
                'Teknik Otomotif',
            ]),
        ];
    }
}

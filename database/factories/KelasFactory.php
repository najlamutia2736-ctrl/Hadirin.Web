<?php

namespace Database\Factories;

use App\Models\Kelas;
use App\Models\MataKuliah;
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
        $namaKelas = fake()->unique()->randomElement(Kelas::KELAS_TERSEDIA);

        return [
            'nama_kelas' => $namaKelas,
            // Nama kelas tidak lagi memuat tingkat di dalamnya, jadi semester
            // diambil langsung dari daftar semester yang tersedia.
            'tingkat' => fake()->randomElement(Kelas::TINGKAT_TERSEDIA),
            'mata_kuliah_id' => MataKuliah::factory(),
            'pembimbing_akademik_id' => null,
            'ruang' => 'R. '.fake()->numberBetween(101, 999),
            'tahun_akademik' => now()->year,
            'status' => 'Aktif',
        ];
    }
}

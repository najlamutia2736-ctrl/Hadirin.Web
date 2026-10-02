<?php

namespace Database\Factories;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Kelas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Jadwal>
 */
class JadwalFactory extends Factory
{
    /**
     * Slot jam pelajaran yang dipakai factory.
     *
     * Daftar ini dipakai bergantian supaya jadwal hasil factory tidak menumpuk
     * semua di jam yang sama dan langsung bentrok dengan jadwal lain.
     *
     * @var list<array{0: string, 1: string}>
     */
    private const SLOT_JAM = [
        ['07:00', '08:30'],
        ['08:30', '10:00'],
        ['10:15', '11:45'],
        ['12:00', '13:30'],
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $slot = fake()->randomElement(self::SLOT_JAM);

        return [
            'kelas_id' => Kelas::factory(),
            'guru_id' => Guru::factory(),
            'mata_pelajaran' => fake()->randomElement([
                'Matematika',
                'Bahasa Indonesia',
                'Fisika',
                'Informatika',
                'Sejarah',
            ]),
            'hari' => fake()->randomElement(Jadwal::HARI_TERSEDIA),
            'jam_mulai' => $slot[0],
            'jam_selesai' => $slot[1],
            'ruang' => 'R. '.fake()->numberBetween(101, 999),
            'tahun_ajaran' => now()->year,
            'status' => 'Aktif',
        ];
    }
}

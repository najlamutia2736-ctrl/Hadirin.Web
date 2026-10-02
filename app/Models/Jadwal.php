<?php

namespace App\Models;

use Database\Factories\JadwalFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu slot jadwal mengajar: mata pelajaran di kelas tertentu, pada hari dan
 * jam tertentu, diajarkan oleh seorang guru.
 *
 * @property int $id
 * @property int $kelas_id
 * @property int $guru_id
 * @property string $mata_pelajaran
 * @property string $hari
 * @property string $jam_mulai
 * @property string $jam_selesai
 * @property string|null $ruang
 * @property int $tahun_ajaran
 * @property string $status
 */
class Jadwal extends Model
{
    /** @use HasFactory<JadwalFactory> */
    use HasFactory;

    /**
     * Nama hari yang dipakai sekolah, sudah urut dari hari kerja.
     *
     * @var list<string>
     */
    public const HARI_TERSEDIA = [
        'Senin',
        'Selasa',
        'Rabu',
        'Kamis',
        'Jumat',
        'Sabtu',
    ];

    /**
     * Status keaktifan jadwal yang dikenali form dan filter.
     *
     * @var list<string>
     */
    public const STATUS_TERSEDIA = [
        'Aktif',
        'Nonaktif',
    ];

    protected $fillable = [
        'kelas_id',
        'guru_id',
        'mata_pelajaran',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'ruang',
        'tahun_ajaran',
        'status',
    ];

    /**
     * Nilai bawaan yang juga menjadi default kolom di database.
     *
     * Tanpa ini, jadwal baru dibuat dalam memori belum punya `status` sampai
     * benar-benar disimpan.
     *
     * @var array<string, string>
     */
    protected $attributes = [
        'status' => 'Aktif',
    ];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    /**
     * Jam pelajaran dalam format `HH:MM` untuk ditampilkan.
     *
     * Kolomnya disimpan sebagai `time`, jadi nilainya bisa berupa `07:30:00`.
     * Format ini merapikan supaya tabel tidak menampilkan detik.
     */
    public function jamMulaiSingkat(): string
    {
        return $this->formatJam($this->jam_mulai);
    }

    public function jamSelesaiSingkat(): string
    {
        return $this->formatJam($this->jam_selesai);
    }

    /**
     * Rentang jam dalam satu teks, mis. `07:30 - 08:15`.
     */
    public function rentangJam(): string
    {
        return $this->jamMulaiSingkat().' - '.$this->jamSelesaiSingkat();
    }

    /**
     * Ubah nilai kolom `time` menjadi `HH:MM`.
     */
    private function formatJam(mixed $nilai): string
    {
        if ($nilai === null || $nilai === '') {
            return '-';
        }

        if ($nilai instanceof DateTimeInterface) {
            return $nilai->format('H:i');
        }

        $waktu = strtotime((string) $nilai);

        return $waktu === false ? '-' : date('H:i', $waktu);
    }
}

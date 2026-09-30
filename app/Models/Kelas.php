<?php

namespace App\Models;

use Database\Factories\KelasFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    /** @use HasFactory<KelasFactory> */
    use HasFactory;

    /**
     * Rombel yang tersedia di sekolah.
     *
     * Daftar ini adalah sumber kebenaran nama kelas. `siswas.kelas` dan
     * `kelas.nama_kelas` memakai nilai yang sama persis supaya relasi
     * `Kelas::siswa()` benar-benar bisa menemukan siswanya.
     *
     * @var list<string>
     */
    public const ROMBEL_TERSEDIA = [
        'X-A',
        'X-B',
        'XI-A',
        'XI-B',
        'XII-A',
        'XII-B',
    ];

    protected $fillable = [
        'nama_kelas',
        'tingkat',
        'jurusan_id',
        'wali_kelas_id',
        'ruang',
        'tahun_ajaran',
        'status',
    ];

    /**
     * Tingkat yang bisa dipilih pada form kelas.
     *
     * @var list<string>
     */
    public const TINGKAT_TERSEDIA = [
        'X',
        'XI',
        'XII',
    ];

    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'wali_kelas_id');
    }

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }

    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class, 'kelas', 'nama_kelas');
    }

    public function guru(): BelongsToMany
    {
        return $this->belongsToMany(Guru::class, 'guru_kelas')->using(GuruKelas::class);
    }
}

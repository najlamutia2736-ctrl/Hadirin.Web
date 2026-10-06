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
     * `Kelas::siswa()` benar-benar bisa menemukan siswanya, dan supaya jumlah
     * siswa di halaman Classes selalu sama dengan isi tabel `siswas`.
     *
     * @var list<string>
     */
    public const ROMBEL_TERSEDIA = [
        'X.1',
        'X.2',
        'X.3',
        'XI.1',
        'XI.2',
        'XI.3',
        'XII.1',
        'XII.2',
        'XII.3',
    ];

    protected $fillable = [
        'nama_kelas',
        'tingkat',
        'mata_pelajaran_id',
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

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
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

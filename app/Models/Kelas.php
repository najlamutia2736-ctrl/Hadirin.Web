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
     * Daftar kelas yang tersedia di kampus.
     *
     * Daftar ini adalah sumber kebenaran nama kelas. `mahasiswa.kelas` dan
     * `kelas.nama_kelas` memakai nilai yang sama persis supaya relasi
     * `Kelas::mahasiswa()` benar-benar bisa menemukan mahasiswanya, dan supaya jumlah
     * mahasiswa di halaman Kelas selalu sama dengan isi tabel `mahasiswa`.
     *
     * @var list<string>
     */
    public const KELAS_TERSEDIA = [
        'Kelas A',
        'Kelas B',
        'Kelas C',
        'Kelas D',
        'Kelas E',
        'Kelas F',
        'Kelas G',
        'Kelas H',
        'Kelas I',
    ];

    protected $fillable = [
        'nama_kelas',
        'tingkat',
        'mata_kuliah_id',
        'pembimbing_akademik_id',
        'ruang',
        'tahun_akademik',
        'status',
    ];

    /**
     * Semester yang bisa dipilih pada form kelas.
     *
     * @var list<string>
     */
    public const TINGKAT_TERSEDIA = [
        'Semester 1',
        'Semester 2',
        'Semester 3',
        'Semester 4',
        'Semester 5',
        'Semester 6',
        'Semester 7',
        'Semester 8',
    ];

    public function pembimbingAkademik(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'pembimbing_akademik_id');
    }

    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }

    public function mahasiswa(): HasMany
    {
        return $this->hasMany(Mahasiswa::class, 'kelas', 'nama_kelas');
    }

    public function dosen(): BelongsToMany
    {
        return $this->belongsToMany(Dosen::class, 'dosen_kelas')->using(DosenKelas::class);
    }
}

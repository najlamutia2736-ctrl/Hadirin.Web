<?php

namespace App\Models;

use Database\Factories\GuruFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guru extends Model
{
    /** @use HasFactory<GuruFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nip',
        'mata_pelajaran',
        'telepon',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Kelas yang menjadi tanggung jawab administratif guru sebagai kepala kelas.
     *
     * Berbeda dengan {@see self::kelasDiampu()}, relasi ini memakai
     * `kelas.wali_kelas_id` dan hanya berisi kelas yang dipegang secara resmi.
     */
    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class, 'wali_kelas_id');
    }

    /**
     * Kelas yang diajar oleh guru, apa pun statusnya sebagai kepala kelas.
     *
     * Inilah relasi yang dipakai dashboard guru untuk membatasi data yang tampil,
     * karena seorang guru bisa mengajar di beberapa kelas sekaligus.
     */
    public function kelasDiampu(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'guru_kelas')->using(GuruKelas::class);
    }
}

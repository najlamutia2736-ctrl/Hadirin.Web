<?php

namespace App\Models;

use Database\Factories\DosenFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dosen extends Model
{
    /** @use HasFactory<DosenFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nidn',
        'mata_kuliah',
        'mata_kuliah_id',
        'telepon',
        'status',
    ];

    /**
     * Mata pelajaran yang diampu, diambil dari tabel `mata_kuliah`.
     *
     * Inilah sumber kebenaran nama & kode mata kuliah seorang dosen.
     * Kolom teks `mata_kuliah` tidak dipakai lagi sebagai rujukan karena
     * tidak terhubung ke tabel mana pun, sehingga bisa menyimpang dari kode
     * mata kuliah yang dipakai di halaman Mata Kuliah dan Classes.
     */
    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }

    /**
     * Nama mata kuliah dosen, siap ditampilkan di mana saja.
     *
     * Mengambil dari relasi `mataKuliah` supaya nama yang tampil di dashboard
     * admin, dashboard dosen, dan laporan berasal dari satu baris yang sama.
     * Kolom teks lama hanya dipakai sebagai cadangan untuk dosen yang mata kuliahnya
     * belum pernah ditentukan lewat form.
     */
    public function namaMataKuliah(): ?string
    {
        return $this->mataKuliah?->nama_mata_kuliah ?? $this->mata_kuliah;
    }

    /**
     * Sinkronkan kolom teks lama mengikuti mata kuliah yang dipilih.
     *
     * Dipakai `saving` supaya nama mata kuliah ikut terisi bahkan saat dosen dibuat
     * lewat factory atau seeding yang tidak melewati form.
     */
    protected static function booted(): void
    {
        static::saving(function (Dosen $dosen): void {
            if ($dosen->mata_kuliah_id === null) {
                return;
            }

            $nama = $dosen->relationLoaded('mataKuliah')
                ? $dosen->mataKuliah?->nama_mata_kuliah
                : MataKuliah::query()->whereKey($dosen->mata_kuliah_id)->value('nama_mata_kuliah');

            if ($nama !== null) {
                $dosen->mata_kuliah = $nama;
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Kelas yang menjadi tanggung jawab administratif dosen sebagai kepala kelas.
     *
     * Berbeda dengan {@see self::kelasDiampu()}, relasi ini memakai
     * `kelas.pembimbing_akademik_id` dan hanya berisi kelas yang dipegang secara resmi.
     */
    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class, 'pembimbing_akademik_id');
    }

    /**
     * Kelas yang diajar oleh dosen, apa pun statusnya sebagai kepala kelas.
     *
     * Inilah relasi yang dipakai dashboard dosen untuk membatasi data yang tampil,
     * karena seorang dosen bisa mengajar di beberapa kelas sekaligus.
     */
    public function kelasDiampu(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'dosen_kelas')->using(DosenKelas::class);
    }
}

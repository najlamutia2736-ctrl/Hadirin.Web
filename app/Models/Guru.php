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
        'mata_pelajaran_id',
        'telepon',
        'status',
    ];

    /**
     * Mata pelajaran yang diampu, diambil dari tabel `mata_pelajaran`.
     *
     * Inilah sumber kebenaran nama & kode mata pelajaran seorang guru.
     * Kolom teks `mata_pelajaran` tidak dipakai lagi sebagai rujukan karena
     * tidak terhubung ke tabel mana pun, sehingga bisa menyimpang dari kode
     * mapel yang dipakai di halaman Subjects dan Classes.
     */
    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    /**
     * Nama mata pelajaran guru, siap ditampilkan di mana saja.
     *
     * Mengambil dari relasi `mataPelajaran` supaya nama yang tampil di dashboard
     * admin, dashboard guru, dan laporan berasal dari satu baris yang sama.
     * Kolom teks lama hanya dipakai sebagai cadangan untuk guru yang mapelnya
     * belum pernah ditentukan lewat form.
     */
    public function namaMataPelajaran(): ?string
    {
        return $this->mataPelajaran?->nama_mata_pelajaran ?? $this->mata_pelajaran;
    }

    /**
     * Sinkronkan kolom teks lama mengikuti mapel yang dipilih.
     *
     * Dipakai `saving` supaya nama mapel ikut terisi bahkan saat guru dibuat
     * lewat factory atau seeding yang tidak melewati form.
     */
    protected static function booted(): void
    {
        static::saving(function (Guru $guru): void {
            if ($guru->mata_pelajaran_id === null) {
                return;
            }

            $nama = $guru->relationLoaded('mataPelajaran')
                ? $guru->mataPelajaran?->nama_mata_pelajaran
                : MataPelajaran::query()->whereKey($guru->mata_pelajaran_id)->value('nama_mata_pelajaran');

            if ($nama !== null) {
                $guru->mata_pelajaran = $nama;
            }
        });
    }

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

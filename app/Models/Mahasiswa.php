<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;  // ← Pakai BelongsTo
use Illuminate\Database\Eloquent\Relations\HasMany;   // ← Pakai HasMany

class Mahasiswa extends Model
{
    /** @use HasFactory<MahasiswaFactory> */
    use HasFactory;

    /**
     * Nama tabel memakai bentuk tunggal, jadi konvensi plural dari Eloquent
     * (`mahasiswas`) tidak berlaku di sini.
     */
    protected $table = 'mahasiswa';

    protected $fillable = ['user_id', 'nim', 'kelas', 'jenis_kelamin', 'wali', 'telepon_wali', 'status'];

    // ===== RELASI =====

    // Mahasiswa milik 1 User (BelongsTo)
    // Karena mahasiswa punya foreign key 'user_id'
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Mahasiswa punya banyak Absensi (HasMany)
    // Karena absensi punya foreign key 'mahasiswa_id'
    public function absensis(): HasMany
    {
        return $this->hasMany(Absensi::class, 'mahasiswa_id');
    }
}

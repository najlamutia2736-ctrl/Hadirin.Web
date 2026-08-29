<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;  // ← Pakai BelongsTo
use Illuminate\Database\Eloquent\Relations\HasMany;   // ← Pakai HasMany

class Siswa extends Model
{
    protected $fillable = ['user_id', 'nisn', 'kelas', 'jurusan'];

    // ===== RELASI =====
    
    // Siswa milik 1 User (BelongsTo) 
    // Karena siswa punya foreign key 'user_id'
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Siswa punya banyak Absensi (HasMany)
    // Karena absensi punya foreign key 'siswa_id'
    public function absensis(): HasMany
    {
        return $this->hasMany(Absensi::class, 'siswa_id');
    }
}
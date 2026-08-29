<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;  // ← Pakai HasMany

class SesiAbsensi extends Model
{
    protected $fillable = ['kode_sesi', 'waktu_mulai', 'waktu_selesai', 'status'];

    // ===== RELASI =====
    
    // SesiAbsensi punya banyak Absensi (HasMany)
    // Karena absensi punya foreign key 'sesi_absensi_id'
    public function absensis(): HasMany
    {
        return $this->hasMany(Absensi::class, 'sesi_absensi_id');
    }
}
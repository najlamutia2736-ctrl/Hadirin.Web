<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;  // ← Pakai BelongsTo (2x)

class Absensi extends Model
{
    protected $fillable = [
        'siswa_id',
        'sesi_absensi_id',
        'waktu_absen',
        'status',
        'keterangan'
    ];

    // ===== RELASI =====
    
    // Absensi milik 1 Siswa (BelongsTo)
    // Karena absensi punya foreign key 'siswa_id'
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    // Absensi milik 1 SesiAbsensi (BelongsTo)
    // Karena absensi punya foreign key 'sesi_absensi_id'
    public function sesiAbsensi(): BelongsTo
    {
        return $this->belongsTo(SesiAbsensi::class, 'sesi_absensi_id');
    }
}
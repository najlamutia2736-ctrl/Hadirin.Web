<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;  // ← Pakai BelongsTo (2x)

class Absensi extends Model
{
    protected $fillable = [
        'mahasiswa_id',
        'sesi_absensi_id',
        'waktu_absen',
        'status',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'waktu_absen' => 'datetime',
        ];
    }

    // ===== RELASI =====

    // Absensi milik 1 Mahasiswa (BelongsTo)
    // Karena absensi punya foreign key 'mahasiswa_id'
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    // Absensi milik 1 SesiAbsensi (BelongsTo)
    // Karena absensi punya foreign key 'sesi_absensi_id'
    public function sesiAbsensi(): BelongsTo
    {
        return $this->belongsTo(SesiAbsensi::class, 'sesi_absensi_id');
    }
}

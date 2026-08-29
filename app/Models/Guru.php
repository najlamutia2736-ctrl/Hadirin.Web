<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;  // ← Pakai BelongsTo

class Guru extends Model
{
    protected $fillable = ['user_id', 'nip', 'mata_pelajaran'];

    // ===== RELASI =====
    
    // Guru milik 1 User (BelongsTo)
    // Karena guru punya foreign key 'user_id'
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
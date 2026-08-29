<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;  // ← Pakai BelongsTo

class Admin extends Model
{
    protected $fillable = ['user_id', 'level'];

    // ===== RELASI =====
    
    // Admin milik 1 User (BelongsTo)
    // Karena admin punya foreign key 'user_id'
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Tabel penghubung dosen dengan kelas yang diampu.
 *
 * Mata pelajaran tidak disimpan di sini karena sudah ada di `dosens.mata_kuliah`,
 * sehingga satu dosen yang mengajar di beberapa kelas selalu mengajar mata kuliah yang sama.
 */
class DosenKelas extends Pivot
{
    public $incrementing = false;

    protected $fillable = [
        'dosen_id',
        'kelas_id',
    ];
}

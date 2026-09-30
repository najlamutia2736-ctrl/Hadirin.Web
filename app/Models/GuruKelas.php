<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Tabel penghubung guru dengan kelas yang diampu.
 *
 * Mata pelajaran tidak disimpan di sini karena sudah ada di `gurus.mata_pelajaran`,
 * sehingga satu guru yang mengajar di beberapa kelas selalu mengajar mapel yang sama.
 */
class GuruKelas extends Pivot
{
    public $incrementing = false;

    protected $fillable = [
        'guru_id',
        'kelas_id',
    ];
}

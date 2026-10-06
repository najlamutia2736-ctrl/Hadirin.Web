<?php

namespace App\Models;

use Database\Factories\MataPelajaranFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MataPelajaran extends Model
{
    /** @use HasFactory<MataPelajaranFactory> */
    use HasFactory;

    /**
     * Tabel `mata_pelajaran` memang dibuat dalam bentuk tunggal oleh migration
     * `create_jurusan_table`, berbeda dari tabel lain di proyek ini yang
     * memakai bentuk jamak. Menyebutkan nama tabelnya secara eksplisit
     * menghindari perlu mengubah skema yang sudah ada.
     */
    protected $table = 'mata_pelajaran';

    protected $fillable = [
        'kode_mata_pelajaran',
        'nama_mata_pelajaran',
    ];

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class, 'mata_pelajaran_id');
    }
}

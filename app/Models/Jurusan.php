<?php

namespace App\Models;

use Database\Factories\JurusanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jurusan extends Model
{
    /** @use HasFactory<JurusanFactory> */
    use HasFactory;

    /**
     * Tabel `jurusan` memang dibuat dalam bentuk tunggal oleh migration
     * `create_jurusan_table`, berbeda dari tabel lain di proyek ini yang
     * memakai bentuk jamak. Menyebutkan nama tabelnya secara eksplisit
     * menghindari perlu mengubah skema yang sudah ada.
     */
    protected $table = 'jurusan';

    protected $fillable = [
        'kode_jurusan',
        'nama_jurusan',
    ];

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class, 'jurusan_id');
    }
}

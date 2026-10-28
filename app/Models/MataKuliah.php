<?php

namespace App\Models;

use Database\Factories\MataKuliahFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MataKuliah extends Model
{
    /** @use HasFactory<MataKuliahFactory> */
    use HasFactory;

    /**
     * Tabel `mata_kuliah` memang dibuat dalam bentuk tunggal oleh migration
     * `create_jurusan_table`, berbeda dari tabel lain di proyek ini yang
     * memakai bentuk jamak. Menyebutkan nama tabelnya secara eksplisit
     * menghindari perlu mengubah skema yang sudah ada.
     */
    protected $table = 'mata_kuliah';

    protected $fillable = [
        'kode_mata_kuliah',
        'nama_mata_kuliah',
    ];

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class, 'mata_kuliah_id');
    }

    /**
     * Dosen yang mengampu mata kuliah ini.
     *
     * Relasinya memakai kolom `dosens.mata_kuliah_id`: satu dosen menunjuk ke
     * satu mata kuliah, tapi satu mata kuliah bisa diampu beberapa dosen.
     * Inilah yang dipakai halaman Manajemen Mata Kuliah untuk menampilkan
     * nama dosen pengajar di setiap baris.
     */
    public function dosens(): HasMany
    {
        return $this->hasMany(Dosen::class, 'mata_kuliah_id');
    }
}

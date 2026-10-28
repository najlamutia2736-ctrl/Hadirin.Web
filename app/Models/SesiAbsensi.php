<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Satu sesi presensi: satu mata kuliah pada satu hari, dengan rentang waktu
 * yang diambil dari jadwal mengajarnya.
 *
 * Satu mahasiswa boleh punya banyak catatan absensi per hari, asalkan beda
 * sesi — jadi presensi benar-benar dihitung per mata kuliah, bukan per hari.
 * Baris lama sebelum perubahan ini tidak punya `jadwal_id` (null), dan
 * `judul()` jatuh ke kode sesi supaya riwayat lama tetap terbaca.
 */
class SesiAbsensi extends Model
{
    /**
     * Nama kelompok untuk catatan presensi lama yang belum punya jadwal.
     *
     * Laporan per mata kuliah mengelompokkan absensi lewat `jadwals.mata_kuliah`.
     * Baris lama sebelum sesi ditautkan ke jadwal (`jadwal_id` null) tetap harus
     * ikut terhitung, jadi digabung ke satu baris dengan nama ini supaya riwayat
     * lama tidak hilang dari rekap.
     */
    public const TANPA_JADWAL = 'Tanpa Jadwal Kuliah';

    /**
     * @var list<string>
     */
    protected $fillable = ['jadwal_id', 'kode_sesi', 'waktu_mulai', 'waktu_selesai', 'status'];

    protected function casts(): array
    {
        return [
            'waktu_mulai' => 'datetime',
            'waktu_selesai' => 'datetime',
        ];
    }

    // ===== RELASI =====

    /**
     * Jadwal mengajar yang memicu sesi ini dibuat.
     */
    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class);
    }

    /**
     * Sesi punya banyak Absensi (HasMany).
     * Karena absensi punya foreign key `sesi_absensi_id`.
     */
    public function absensis(): HasMany
    {
        return $this->hasMany(Absensi::class, 'sesi_absensi_id');
    }

    // ===== PERILAKU =====

    /**
     * Nama mata kuliah untuk tampilan.
     *
     * Sesi lama tanpa jadwal tetap terbaca lewat `kode_sesi`-nya, misalnya
     * `HARIAN-2026-10-09`.
     */
    public function judul(): string
    {
        return $this->jadwal?->mata_kuliah ?? $this->kode_sesi;
    }

    /**
     * Posisi sesi terhadap waktu sekarang.
     *
     * `belum`  = jadwal belum dimulai, `berjalan` = rentang waktunya sedang
     * mencakup saat ini, `selesai` = jam selesai sudah lewat. Kolom `status`
     * tidak dipakai di sini karena bisa tertinggal kalau scheduler belum jalan.
     *
     * @param  Carbon|null  $saat  waktu pembanding, default sekarang
     * @return 'belum'|'berjalan'|'selesai'
     */
    public function kondisiSekarang(?Carbon $saat = null): string
    {
        $saat = $saat ?? Carbon::now();

        if ($saat->lessThan($this->waktu_mulai)) {
            return 'belum';
        }

        if ($saat->greaterThan($this->waktu_selesai)) {
            return 'selesai';
        }

        return 'berjalan';
    }
}

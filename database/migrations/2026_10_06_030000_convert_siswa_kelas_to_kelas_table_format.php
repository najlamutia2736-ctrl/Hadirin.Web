<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Menyamakan nilai `siswas.kelas` dengan `kelas.nama_kelas`.
 *
 * Sebelumnya `siswas.kelas` memakai akhiran huruf ("XII-A") sementara tabel
 * `kelas` memakai akhiran angka ("XII.1"). Karena keduanya dibandingkan sebagai
 * teks pada relasi `Kelas::siswa()`, nilai yang tidak sama membuat jumlah siswa
 * di halaman Classes selalu nol.
 *
 * Hanya nama kelas yang diubah, tidak ada baris yang ditambah atau dihapus.
 */
return new class extends Migration
{
    /**
     * Pemetaan nama lama ke nama baru.
     *
     * Hanya dipakai kalau nama barunya memang ada di tabel `kelas`, supaya
     * kelas yang tidak punya padanan tidak dipaksa menunjuk ke kelas lain.
     *
     * @var array<string, string>
     */
    private const PETA_KELAS = [
        'X-A' => 'X.1',
        'X-B' => 'X.2',
        'XI-A' => 'XI.1',
        'XI-B' => 'XI.2',
        'XII-A' => 'XII.1',
        'XII-B' => 'XII.2',
    ];

    public function up(): void
    {
        $tersedia = DB::table('kelas')->pluck('nama_kelas')->all();

        foreach (self::PETA_KELAS as $lama => $baru) {
            if (in_array($baru, $tersedia, true)) {
                DB::table('siswas')->where('kelas', $lama)->update(['kelas' => $baru]);
            }
        }
    }

    public function down(): void
    {
        $tersedia = DB::table('kelas')->pluck('nama_kelas')->all();

        foreach (array_flip(self::PETA_KELAS) as $baru => $lama) {
            if (in_array($baru, $tersedia, true)) {
                DB::table('siswas')->where('kelas', $baru)->update(['kelas' => $lama]);
            }
        }
    }
};

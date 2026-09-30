<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Daftar jurusan yang dipakai sekolah, sebagai `kode_jurusan` => `nama_jurusan`.
     *
     * @var array<string, string>
     */
    private const JURUSAN = [
        'RPL' => 'Rekayasa Perangkat Lunak',
        'FARMASI' => 'Farmasi',
        'DKV' => 'Desain Komunikasi Visual',
        'TJKT' => 'Teknik Jaringan Komputer dan Telekomunikasi',
        'KIMIA' => 'Kimia Industri',
        'BUSANA' => 'Tata Busana',
    ];

    /**
     * Pemetaan nama kelas lama ke rombel baru beserta kode jurusan asalnya.
     *
     * Sebelumnya `kelas.nama_kelas` diisi nama jurusan (mis. "RPL") sementara
     * `siswas.kelas` diisi rombel (mis. "XII-A"). Karena keduanya tidak pernah
     * sama, relasi `Kelas::siswa()` selalu mengembalikan nol siswa. Nama
     * jurusan dipindah ke kolom `jurusan_id`, dan `nama_kelas` diisi rombel
     * supaya keduanya bisa bertemu.
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const PETA_KELAS = [
        'RPL' => ['XII-A', 'RPL'],
        'Farmasi' => ['XII-B', 'FARMASI'],
        'DKV' => ['X-A', 'DKV'],
        'TJKT' => ['XI-B', 'TJKT'],
        'Kimia Industri' => ['X-B', 'KIMIA'],
        'Tata Busana' => ['XI-A', 'BUSANA'],
    ];

    public function up(): void
    {
        // DDL di MySQL tidak ikut rollback, jadi kolom dicek dulu agar migration
        // tetap aman dijalankan ulang setelah percobaan yang gagal di tengah jalan.
        if (! Schema::hasColumn('kelas', 'jurusan_id')) {
            Schema::table('kelas', function (Blueprint $table): void {
                $table->foreignId('jurusan_id')
                    ->nullable()
                    ->after('nama_kelas')
                    ->constrained('jurusan')
                    ->nullOnDelete();
            });
        }

        foreach (self::JURUSAN as $kode => $nama) {
            DB::table('jurusan')->updateOrInsert(
                ['kode_jurusan' => $kode],
                ['nama_jurusan' => $nama],
            );
        }

        $idJurusan = DB::table('jurusan')->pluck('id', 'kode_jurusan');

        foreach (self::PETA_KELAS as $namaLama => [$rombel, $kodeJurusan]) {
            DB::table('kelas')
                ->where('nama_kelas', $namaLama)
                ->update([
                    'nama_kelas' => $rombel,
                    'jurusan_id' => $idJurusan[$kodeJurusan] ?? null,
                ]);
        }
    }

    public function down(): void
    {
        foreach (self::PETA_KELAS as $namaLama => [$rombel]) {
            DB::table('kelas')
                ->where('nama_kelas', $rombel)
                ->update(['nama_kelas' => $namaLama]);
        }

        DB::table('jurusan')->whereIn('kode_jurusan', array_keys(self::JURUSAN))->delete();

        if (Schema::hasColumn('kelas', 'jurusan_id')) {
            Schema::table('kelas', function (Blueprint $table): void {
                $table->dropForeign(['jurusan_id']);
                $table->dropColumn('jurusan_id');
            });
        }
    }
};

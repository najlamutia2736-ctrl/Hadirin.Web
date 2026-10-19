<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Mengubah seluruh penamaan dari istilah sekolah ke istilah universitas.
 *
 * Yang dipindahkan:
 * - `siswas` -> `mahasiswa`, `gurus` -> `dosens`, `guru_kelas` -> `dosen_kelas`,
 *   `mata_pelajaran` -> `mata_kuliah`.
 * - `siswas.nisn` -> `mahasiswa.nim`, `gurus.nip` -> `dosens.nidn`.
 * - `wali_kelas_id` -> `pembimbing_akademik_id`, `guru_pengampu` ->
 *   `dosen_pengampu`, `tahun_ajaran` -> `tahun_akademik`.
 * - Nilai peran di `users.role`: `Siswa` -> `Mahasiswa`, `Guru` -> `Dosen`.
 *
 * Kolom `siswas.mata_pelajaran` dibuang karena mahasiswa tidak lagi punya satu
 * mata pelajaran tunggal seperti di sekolah.
 *
 * Isi data lama tetap dipertahankan, hanya nama dan nilainya yang ditulis ulang
 * supaya cocok dengan istilah baru. Tabel dan kolom lama sengaja tidak diubah di
 * migration aslinya supaya instalasi yang sudah terlanjur memakai skema lama tidak
 * kehilangan data ketika migration ini dijalankan.
 */
return new class extends Migration
{
    /**
     * Peta nama kelas lama ke nama kelas baru.
     *
     * @var array<string, string>
     */
    private const PETA_KELAS = [
        'X.1' => 'Kelas A',
        'X.2' => 'Kelas B',
        'X.3' => 'Kelas C',
        'XI.1' => 'Kelas D',
        'XI.2' => 'Kelas E',
        'XI.3' => 'Kelas F',
        'XII.1' => 'Kelas G',
        'XII.2' => 'Kelas H',
        'XII.3' => 'Kelas I',
    ];

    /**
     * Peta tingkat lama ke semester baru.
     *
     * @var array<string, string>
     */
    private const PETA_TINGKAT = [
        'X' => 'Semester 1',
        'XI' => 'Semester 2',
        'XII' => 'Semester 3',
    ];

    public function up(): void
    {
        // Constraint foreign key harus dilepas dulu supaya nama constraint tidak
        // ikut tersangkut saat kolom dan tabelnya diganti nama.
        $foreignKeys = [
            ['absensis', 'siswa_id'],
            ['guru_kelas', 'guru_id'],
            ['gurus', 'mata_pelajaran_id'],
            ['jadwals', 'guru_id'],
            ['kelas', 'mata_pelajaran_id'],
            ['kelas', 'wali_kelas_id'],
        ];

        foreach ($foreignKeys as [$tabel, $kolom]) {
            $this->dropForeignKeyIfExists($tabel, $kolom);
        }

        // Nilai data ditulis ulang lebih dulu supaya `mahasiswas.kelas` dan
        // `kelas.nama_kelas` tetap memakai nilai yang sama persis seperti
        // sebelumnya. Relasi `Kelas::mahasiswa()` bergantung pada itu.
        $this->rewriteNilaiKelas();

        // Kolom yang isinya tidak relevan di kampus dibuang lebih dulu supaya
        // tidak ikut membawa nama lama ke skema baru.
        if (Schema::hasColumn('siswas', 'mata_pelajaran')) {
            Schema::table('siswas', function (Blueprint $table): void {
                $table->dropColumn('mata_pelajaran');
            });
        }

        $this->renameColumns();

        $this->renameTables();

        // MySQL sudah otomatis memberi nama index unique baru mengikuti nama
        // kolom dan tabel barunya, jadi yang dibersihkan di sini hanya nama
        // constraint yang masih tertinggal dari skema lama.
        $this->renameConstraint('siswas', 'siswas_user_id_foreign', 'mahasiswa_user_id_foreign');
        $this->renameConstraint('gurus', 'gurus_user_id_foreign', 'dosens_user_id_foreign');

        $this->addForeignKeys();

        DB::table('users')->where('role', 'Siswa')->update(['role' => 'Mahasiswa']);
        DB::table('users')->where('role', 'Guru')->update(['role' => 'Dosen']);
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'Mahasiswa')->update(['role' => 'Siswa']);
        DB::table('users')->where('role', 'Dosen')->update(['role' => 'Guru']);

        $foreignKeys = [
            ['absensis', 'mahasiswa_id'],
            ['dosen_kelas', 'dosen_id'],
            ['dosens', 'mata_kuliah_id'],
            ['jadwals', 'dosen_id'],
            ['kelas', 'mata_kuliah_id'],
            ['kelas', 'pembimbing_akademik_id'],
        ];

        foreach ($foreignKeys as [$tabel, $kolom]) {
            $this->dropForeignKeyIfExists($tabel, $kolom);
        }

        $this->renameTables([
            'mahasiswa' => 'siswas',
            'dosens' => 'gurus',
            'dosen_kelas' => 'guru_kelas',
            'mata_kuliah' => 'mata_pelajaran',
        ]);

        $this->renameColumns(true);

        $this->addForeignKeys(true);

        $this->rewriteNilaiKelas(true);
    }

    /**
     * Ganti nama kolom lama ke kolom baru per tabel.
     */
    private function renameColumns(bool $kebalikan = false): void
    {
        $peta = [
            'gurus' => ['nip' => 'nidn', 'mata_pelajaran' => 'mata_kuliah', 'mata_pelajaran_id' => 'mata_kuliah_id'],
            'guru_kelas' => ['guru_id' => 'dosen_id'],
            'kelas' => [
                'mata_pelajaran_id' => 'mata_kuliah_id',
                'wali_kelas_id' => 'pembimbing_akademik_id',
                'guru_pengampu' => 'dosen_pengampu',
                'tahun_ajaran' => 'tahun_akademik',
            ],
            'jadwals' => ['guru_id' => 'dosen_id', 'mata_pelajaran' => 'mata_kuliah', 'tahun_ajaran' => 'tahun_akademik'],
            'absensis' => ['siswa_id' => 'mahasiswa_id'],
            'siswas' => ['nisn' => 'nim'],
            'mata_pelajaran' => ['kode_mata_pelajaran' => 'kode_mata_kuliah', 'nama_mata_pelajaran' => 'nama_mata_kuliah'],
        ];

        // `guru_kelas` sudah jadi `dosen_kelas` ketika `up()` berjalan, dan
        // sebaliknya ketika `down()` berjalan, jadi tabelnya dicari dua nama.
        $peta['dosen_kelas'] = $peta['guru_kelas'];
        $peta['mahasiswa'] = $peta['siswas'];
        $peta['mata_kuliah'] = $peta['mata_pelajaran'];

        foreach ($peta as $tabel => $kolomKolom) {
            if (! Schema::hasTable($tabel)) {
                continue;
            }

            foreach ($kolomKolom as $lama => $baru) {
                $dari = $kebalikan ? $baru : $lama;
                $ke = $kebalikan ? $lama : $baru;

                if (! Schema::hasColumn($tabel, $dari)) {
                    continue;
                }

                Schema::table($tabel, function (Blueprint $table) use ($dari, $ke): void {
                    $table->renameColumn($dari, $ke);
                });
            }
        }
    }

    /**
     * Ganti nama tabel lama ke tabel baru.
     *
     * @param  array<string, string>|null  $peta  Kosongkan untuk memakai peta bawaan.
     */
    private function renameTables(?array $peta = null): void
    {
        $peta ??= [
            'siswas' => 'mahasiswa',
            'gurus' => 'dosens',
            'guru_kelas' => 'dosen_kelas',
            'mata_pelajaran' => 'mata_kuliah',
        ];

        foreach ($peta as $lama => $baru) {
            if (Schema::hasTable($lama)) {
                Schema::rename($lama, $baru);
            }
        }
    }

    /**
     * Ganti nama constraint yang masih tertinggal dari skema lama.
     *
     * MySQL menyediakan `RENAME INDEX` tapi tidak ada padanan untuk foreign key,
     * jadi constraint foreign key dibuang lalu dipasang ulang dengan nama baru
     * di `addForeignKeys()`. Yang ditangani di sini hanya index biasa dan unique.
     */
    private function renameConstraint(string $tabel, string $indexLama, string $indexBaru): void
    {
        if (! Schema::hasTable($tabel) || $indexLama === $indexBaru) {
            return;
        }

        // `RENAME INDEX` hanya ada di MySQL. Di SQLite, yang dipanggil hanya
        // `Schema::rename()`, dan index ikut terbawa oleh nama kolom barunya.
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE `{$tabel}` RENAME INDEX `{$indexLama}` TO `{$indexBaru}`");
    }

    /**
     * Pasang kembali foreign key sesuai nama kolom yang sedang dipakai.
     */
    private function addForeignKeys(bool $kebalikan = false): void
    {
        // Aturan `onDelete` disalin apa adanya dari constraint aslinya supaya
        // perilaku cascade hapus data tidak berubah sama sekali.
        $peta = [
            ['absensis', 'mahasiswa_id', 'mahasiswa', 'id', 'cascade'],
            ['dosen_kelas', 'dosen_id', 'dosens', 'id', 'cascade'],
            ['dosens', 'mata_kuliah_id', 'mata_kuliah', 'id', 'set null'],
            ['jadwals', 'dosen_id', 'dosens', 'id', 'cascade'],
            ['kelas', 'mata_kuliah_id', 'mata_kuliah', 'id', 'set null'],
            ['kelas', 'pembimbing_akademik_id', 'dosens', 'id', 'set null'],
        ];

        if ($kebalikan) {
            $peta = [
                ['absensis', 'siswa_id', 'siswas', 'id', 'cascade'],
                ['guru_kelas', 'guru_id', 'gurus', 'id', 'cascade'],
                ['gurus', 'mata_pelajaran_id', 'mata_pelajaran', 'id', 'set null'],
                ['jadwals', 'guru_id', 'gurus', 'id', 'cascade'],
                ['kelas', 'mata_pelajaran_id', 'mata_pelajaran', 'id', 'set null'],
                ['kelas', 'wali_kelas_id', 'gurus', 'id', 'set null'],
            ];
        }

        foreach ($peta as [$tabel, $kolom, $rujukan, $rujukKolom, $onDelete]) {
            if (! Schema::hasTable($tabel) || ! Schema::hasColumn($tabel, $kolom) || ! Schema::hasTable($rujukan)) {
                continue;
            }

            Schema::table($tabel, function (Blueprint $table) use ($kolom, $rujukan, $rujukKolom, $onDelete): void {
                $table->foreign($kolom)->references($rujukKolom)->on($rujukan)->onDelete($onDelete);
            });
        }
    }

    /**
     * Buang foreign key hanya kalau memang ada, supaya migration aman jalan
     * dua kali maupun di instalasi yang skemanya sedikit berbeda.
     */
    private function dropForeignKeyIfExists(string $tabel, string $kolom): void
    {
        if (! Schema::hasTable($tabel) || ! Schema::hasColumn($tabel, $kolom)) {
            return;
        }

        // SQLite tidak mendukung drop foreign key by name, dan `renameColumn`
        // di SQLite sudah ikut menulis ulang constraint-nya sendiri. Jadi di
        // sana cukup diserahkan ke Illuminate.
        if (DB::connection()->getDriverName() !== 'mysql') {
            Schema::table($tabel, function (Blueprint $table) use ($kolom): void {
                $table->dropForeign([$kolom]);
            });

            return;
        }

        // Nama constraint sebenarnya dibaca dari database, bukan ditebak dari
        // nama tabel dan kolom. Kalau kolom sudah sempat di-rename, nama
        // constraint lamanya masih tertinggal dan tidak bisa ditebak dari nama
        // kolom baru.
        foreach (Schema::getForeignKeys($tabel) as $foreignKey) {
            if (! in_array($kolom, (array) ($foreignKey['columns'] ?? []), true)) {
                continue;
            }

            Schema::table($tabel, function (Blueprint $table) use ($foreignKey): void {
                // Nama constraint dikirim sebagai string, bukan array. Array akan
                // dibaca sebagai peta kolom danikut menambahkan awalan nama tabel.
                $table->dropForeign($foreignKey['name']);
            });
        }
    }

    /**
     * Tulis ulang nilai kelas dan tingkat lama menjadi padanan kampusnya.
     */
    private function rewriteNilaiKelas(bool $kebalikan = false): void
    {
        $petaKelas = $kebalikan ? array_flip(self::PETA_KELAS) : self::PETA_KELAS;
        $petaTingkat = $kebalikan ? array_flip(self::PETA_TINGKAT) : self::PETA_TINGKAT;

        $tabelMahasiswa = Schema::hasTable('mahasiswa') ? 'mahasiswa' : 'siswas';

        if (Schema::hasTable($tabelMahasiswa) && Schema::hasColumn($tabelMahasiswa, 'kelas')) {
            foreach ($petaKelas as $lama => $baru) {
                DB::table($tabelMahasiswa)->where('kelas', $lama)->update(['kelas' => $baru]);
            }
        }

        if (Schema::hasTable('kelas')) {
            foreach ($petaKelas as $lama => $baru) {
                DB::table('kelas')->where('nama_kelas', $lama)->update(['nama_kelas' => $baru]);
            }

            foreach ($petaTingkat as $lama => $baru) {
                DB::table('kelas')->where('tingkat', $lama)->update(['tingkat' => $baru]);
            }
        }
    }
};

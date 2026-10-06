<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mengganti penamaan "jurusan" menjadi "mata pelajaran" di seluruh skema.
 *
 * Yang diubah hanya nama tabel dan kolom, isi datanya tidak disentuh sama
 * sekali, jadi halaman CMS tetap memakai data yang sama seperti sebelumnya.
 * Tabel dan kolom lama sengaja tidak diubah di migration aslinya supaya
 * instalasi yang sudah terlanjur memakai skema lama tidak kehilangan data
 * ketika migration ini dijalankan.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Foreign key harus dilepas dulu supaya nama constraint tidak ikut
        // tersangkut saat kolom dan tabelnya diganti nama.
        if (Schema::hasColumn('kelas', 'jurusan_id')) {
            Schema::table('kelas', function (Blueprint $table): void {
                $table->dropForeign(['jurusan_id']);
            });
        }

        if (Schema::hasColumn('kelas', 'jurusan_id')) {
            Schema::table('kelas', function (Blueprint $table): void {
                $table->renameColumn('jurusan_id', 'mata_pelajaran_id');
            });
        }

        if (Schema::hasTable('jurusan') && Schema::hasColumn('jurusan', 'kode_jurusan')) {
            Schema::table('jurusan', function (Blueprint $table): void {
                $table->renameColumn('kode_jurusan', 'kode_mata_pelajaran');
            });
        }

        if (Schema::hasTable('jurusan') && Schema::hasColumn('jurusan', 'nama_jurusan')) {
            Schema::table('jurusan', function (Blueprint $table): void {
                $table->renameColumn('nama_jurusan', 'nama_mata_pelajaran');
            });
        }

        if (Schema::hasColumn('siswas', 'jurusan')) {
            Schema::table('siswas', function (Blueprint $table): void {
                $table->renameColumn('jurusan', 'mata_pelajaran');
            });
        }

        if (Schema::hasTable('jurusan')) {
            Schema::rename('jurusan', 'mata_pelajaran');
        }

        if (Schema::hasColumn('kelas', 'mata_pelajaran_id')) {
            Schema::table('kelas', function (Blueprint $table): void {
                $table->foreign('mata_pelajaran_id')
                    ->references('id')
                    ->on('mata_pelajaran')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('kelas', 'mata_pelajaran_id')) {
            Schema::table('kelas', function (Blueprint $table): void {
                $table->dropForeign(['mata_pelajaran_id']);
            });
        }

        if (Schema::hasTable('mata_pelajaran')) {
            Schema::rename('mata_pelajaran', 'jurusan');
        }

        if (Schema::hasColumn('jurusan', 'kode_mata_pelajaran')) {
            Schema::table('jurusan', function (Blueprint $table): void {
                $table->renameColumn('kode_mata_pelajaran', 'kode_jurusan');
            });
        }

        if (Schema::hasColumn('jurusan', 'nama_mata_pelajaran')) {
            Schema::table('jurusan', function (Blueprint $table): void {
                $table->renameColumn('nama_mata_pelajaran', 'nama_jurusan');
            });
        }

        if (Schema::hasColumn('siswas', 'mata_pelajaran')) {
            Schema::table('siswas', function (Blueprint $table): void {
                $table->renameColumn('mata_pelajaran', 'jurusan');
            });
        }

        if (Schema::hasColumn('kelas', 'mata_pelajaran_id')) {
            Schema::table('kelas', function (Blueprint $table): void {
                $table->renameColumn('mata_pelajaran_id', 'jurusan_id');
            });

            Schema::table('kelas', function (Blueprint $table): void {
                $table->foreign('jurusan_id')
                    ->references('id')
                    ->on('jurusan')
                    ->nullOnDelete();
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menghubungkan guru ke tabel `mata_pelajaran`.
 *
 * Sebelumnya `gurus.mata_pelajaran` hanya kolom teks bebas, sehingga nama
 * mata pelajaran yang sama bisa ditulis dengan ejaan berbeda di guru, di
 * kelas, dan di jadwal. Akibatnya dashboard admin dan dashboard guru bisa
 * menampilkan mata pelajaran yang berbeda untuk guru yang sama.
 *
 * Kolom ini menambahkan `mata_pelajaran_id` sebagai sumber kebenaran baru,
 * lalu mengisi ulang datanya dari teks lama dengan pencocokan nama. Kolom
 * teksnya sengaja dipertahankan supaya data lama tidak hilang, dan tetap
 * disinkronkan oleh model `Guru`.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('gurus', 'mata_pelajaran_id')) {
            return;
        }

        Schema::table('gurus', function (Blueprint $table): void {
            $table->foreignId('mata_pelajaran_id')
                ->nullable()
                ->after('mata_pelajaran')
                ->constrained('mata_pelajaran')
                ->nullOnDelete();
        });

        $this->isiMataPelajaranId();
    }

    /**
     * Backfill `gurus.mata_pelajaran_id` dari teks `gurus.mata_pelajaran`.
     *
     * Pencocokan dilakukan tanpa regards case supaya "Bahasa indonesia" di
     * `mata_pelajaran` tetap cocok dengan "Bahasa Indonesia" di `gurus`.
     * Guru yang teksnya tidak ada padanan tetap dibiarkan null supaya bisa
     * ditentukan manual lewat form guru.
     */
    protected function isiMataPelajaranId(): void
    {
        if (! Schema::hasTable('mata_pelajaran')) {
            return;
        }

        DB::table('mata_pelajaran')
            ->orderBy('id')
            ->get(['id', 'nama_mata_pelajaran'])
            ->each(function ($mataPelajaran): void {
                DB::table('gurus')
                    ->whereRaw('LOWER(mata_pelajaran) = ?', [mb_strtolower($mataPelajaran->nama_mata_pelajaran)])
                    ->whereNull('mata_pelajaran_id')
                    ->update(['mata_pelajaran_id' => $mataPelajaran->id]);
            });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('gurus', 'mata_pelajaran_id')) {
            return;
        }

        Schema::table('gurus', function (Blueprint $table): void {
            $table->dropForeign(['mata_pelajaran_id']);
            $table->dropColumn('mata_pelajaran_id');
        });
    }
};

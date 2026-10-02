<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Jadwal mengajar: satu baris = satu mata pelajaran di satu kelas, pada
     * hari dan jam tertentu,Diajarkan oleh seorang guru.
     */
    public function up(): void
    {
        Schema::create('jadwals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('kelas_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained()->cascadeOnDelete();

            $table->string('mata_pelajaran', 100);
            $table->string('hari', 10);
            $table->time('jam_mulai');
            $table->time('jam_selesai');

            $table->string('ruang', 50)->nullable();
            $table->unsignedSmallInteger('tahun_ajaran');
            $table->string('status', 20)->default('Aktif');

            $table->timestamps();

            // Jadwal hampir selalu disaring per hari, per kelas, atau per guru,
            // jadi kolom yang dipakai di WHERE itu berindeks.
            $table->index(['kelas_id', 'hari']);
            $table->index(['guru_id', 'hari']);
            $table->index(['tahun_ajaran', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwals');
    }
};

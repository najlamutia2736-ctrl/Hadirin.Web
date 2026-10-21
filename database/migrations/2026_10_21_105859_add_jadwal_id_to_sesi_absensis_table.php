<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Sesi absensi berpindah dari "satu sesi per hari" menjadi "satu sesi per
     * mata kuliah per hari". Kolom `jadwal_id` menautkan sesi ke jadwal
     * mengajar yang melahirkannya, sehingga sesi tahu sendiri milik kelas
     * mana, diajarkan dosen mana, dan pada jam berapa.
     *
     * Baris lama (sesi harian) dibiarkan dengan `jadwal_id` null supaya
     * riwayat absensi lama tidak rusak; relasi ke jadwal bersifat null saat
     * jadwalnya dihapus supaya sesi ikut kehilangan rujukan, bukan ikut
     * terhapus beserta catatan absensinya.
     */
    public function up(): void
    {
        Schema::table('sesi_absensis', function (Blueprint $table) {
            $table->foreignId('jadwal_id')->nullable()->after('id')
                ->constrained('jadwals')->nullOnDelete();

            // Kunci unik ini yang membuat pembuatan sesi idempoten di level
            // database: satu jadwal hanya boleh punya satu sesi per tanggal,
            // walau dua permintaan datang bersamaan.
            $table->unique(['jadwal_id', 'kode_sesi']);

            // Penutupan sesi dan daftar sesi hari ini selalu menyaring tanggal.
            $table->index('waktu_mulai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sesi_absensis', function (Blueprint $table) {
            $table->dropIndex(['jadwal_id', 'kode_sesi']);
            $table->dropIndex(['waktu_mulai']);
            $table->dropConstrainedForeignId('jadwal_id');
        });
    }
};

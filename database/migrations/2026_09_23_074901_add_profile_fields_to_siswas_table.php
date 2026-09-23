<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->string('jenis_kelamin', 1)->after('nisn');
            $table->string('wali')->nullable()->after('jenis_kelamin');
            $table->string('telepon_wali')->nullable()->after('wali');
            $table->string('status')->default('Aktif')->after('jurusan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn(['jenis_kelamin', 'wali', 'telepon_wali', 'status']);
        });
    }
};

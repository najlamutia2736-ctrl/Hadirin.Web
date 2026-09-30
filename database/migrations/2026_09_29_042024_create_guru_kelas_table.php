<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guru_kelas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('guru_id')->constrained('gurus')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['guru_id', 'kelas_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_kelas');
    }
};

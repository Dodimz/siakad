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
        Schema::create('calon_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kampus_id')->constrained('kampus')->cascadeOnDelete();
            $table->string('no_pendaftar');
            $table->string('nama');
            $table->string('email')->nullable();
            $table->string('telepon', 30)->nullable();
            $table->string('jalur')->nullable();
            $table->string('tahun_masuk');
            $table->string('prodi_nama')->nullable();
            $table->json('payload')->nullable();
            $table->string('status')->default('pending'); // pending, imported
            $table->timestamps();

            $table->unique(['kampus_id', 'no_pendaftar']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calon_mahasiswa');
    }
};

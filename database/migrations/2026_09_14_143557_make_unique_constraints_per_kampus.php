<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Normalisasi constraint agar unik per kampus, bukan global.
     * users.email tetap unik global karena menjadi identitas login.
     */
    public function up(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropUnique('mahasiswa_nim_unique');
            $table->unique(['kampus_id', 'nim'], 'mahasiswa_kampus_nim_unique');
        });

        Schema::table('dosen', function (Blueprint $table) {
            $table->dropUnique('dosen_nidn_unique');
            $table->unique(['kampus_id', 'nidn'], 'dosen_kampus_nidn_unique');
        });

        Schema::table('mata_kuliah', function (Blueprint $table) {
            $table->dropUnique('mata_kuliah_kode_mk_unique');
            $table->unique(['kampus_id', 'kode_mk'], 'mata_kuliah_kampus_kode_unique');
        });

        Schema::table('ruangan', function (Blueprint $table) {
            $table->dropUnique('ruangan_kode_ruangan_unique');
            $table->unique(['kampus_id', 'kode_ruangan'], 'ruangan_kampus_kode_unique');
        });

        Schema::table('tahun_akademik', function (Blueprint $table) {
            $table->unique(['kampus_id', 'tahun', 'semester'], 'tahun_akademik_kampus_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropUnique('mahasiswa_kampus_nim_unique');
            $table->unique('nim');
        });

        Schema::table('dosen', function (Blueprint $table) {
            $table->dropUnique('dosen_kampus_nidn_unique');
            $table->unique('nidn');
        });

        Schema::table('mata_kuliah', function (Blueprint $table) {
            $table->dropUnique('mata_kuliah_kampus_kode_unique');
            $table->unique('kode_mk');
        });

        Schema::table('ruangan', function (Blueprint $table) {
            $table->dropUnique('ruangan_kampus_kode_unique');
            $table->unique('kode_ruangan');
        });

        Schema::table('tahun_akademik', function (Blueprint $table) {
            $table->dropUnique('tahun_akademik_kampus_unique');
        });
    }
};

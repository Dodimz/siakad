<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $tables = [
        'users',
        'fakultas',
        'prodi',
        'mahasiswa',
        'dosen',
        'tahun_akademik',
        'mata_kuliah',
        'kelas',
        'ruangan',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->foreignId('kampus_id')
                    ->nullable()
                    ->constrained('kampus')
                    ->cascadeOnDelete()
                    ->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropConstrainedForeignId('kampus_id');
            });
        }
    }
};

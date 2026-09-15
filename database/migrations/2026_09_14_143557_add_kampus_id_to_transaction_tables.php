<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $tables = [
        'krs',
        'krs_detail',
        'nilai',
        'jadwal_kuliah',
        'pertemuan',
        'presensi',
        'skripsi',
        'bimbingan_skripsi',
        'kerja_praktek',
        'logbook_kp',
        'kehadiran_dosen',
        'materi',
        'tugas',
        'tugas_submission',
        'activity_log',
        'notifications',
        'ai_conversation_logs',
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
                    ->cascadeOnDelete();
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

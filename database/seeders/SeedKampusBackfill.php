<?php

namespace Database\Seeders;

use App\Models\Kampus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SeedKampusBackfill extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Membuat kampus default lalu mengisi kampus_id pada seluruh data
     * yang sudah ada (migrasi single-campus ke multi-campus).
     */
    public function run(): void
    {
        $kampus = Kampus::firstOrCreate(
            ['slug' => 'kampus-utama'],
            [
                'nama' => 'Kampus Utama',
                'kode' => 'KU',
                'status' => 'active',
            ]
        );

        $kampusId = $kampus->id;

        $tables = [
            'users',
            'fakultas',
            'prodi',
            'mahasiswa',
            'dosen',
            'tahun_akademik',
            'mata_kuliah',
            'kelas',
            'ruangan',
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

        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasColumn($table, 'kampus_id')) {
                DB::table($table)
                    ->whereNull('kampus_id')
                    ->update(['kampus_id' => $kampusId]);
            }
        }

        $this->command->info('Backfill kampus_id berhasil untuk kampus: '.$kampus->nama.' ('.$kampus->slug.')');
    }
}

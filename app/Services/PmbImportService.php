<?php

namespace App\Services;

use App\Models\CalonMahasiswa;
use App\Models\Kampus;

class PmbImportService
{
    /**
     * Simpan data calon mahasiswa dari sistem PMB eksternal.
     * Idempoten: no_pendaftar yang sama per kampus tidak dibuat dua kali.
     */
    public function importCalon(array $data, Kampus $kampus): CalonMahasiswa
    {
        $payload = $data['payload'] ?? null;

        unset($data['payload']);

        $data['kampus_id'] = $kampus->id;

        $data['payload'] = $payload;

        return CalonMahasiswa::firstOrCreate(
            ['kampus_id' => $kampus->id, 'no_pendaftar' => $data['no_pendaftar']],
            $data,
        );
    }
}

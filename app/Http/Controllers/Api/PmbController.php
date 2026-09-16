<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kampus;
use App\Models\PmbImportLog;
use App\Services\PmbImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PmbController extends Controller
{
    public function __construct(
        protected PmbImportService $importer,
    ) {}

    /**
     * Terima data calon mahasiswa yang di-push sistem PMB eksternal.
     *
     * Payload yang diterima:
     * - no_pendaftar (required) nomor unik pendaftaran dari PMB
     * - nama (required)
     * - email (nullable, format email)
     * - telepon (nullable)
     * - jalur (nullable) mis. snbt, reguler, beasiswa
     * - tahun_masuk (required) mis. 2026
     * - prodi_nama (nullable)
     * - payload (nullable, array) data mentah tambahan dari PMB
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'no_pendaftar' => ['required', 'string', 'max:100'],
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:30'],
            'jalur' => ['nullable', 'string', 'max:100'],
            'tahun_masuk' => ['required', 'string', 'max:10'],
            'prodi_nama' => ['nullable', 'string', 'max:255'],
            'payload' => ['nullable', 'array'],
        ]);

        /** @var Kampus $kampus */
        $kampus = $request->attributes->get('pmb_kampus');

        $calon = $this->importer->importCalon($validated, $kampus);

        $isDuplicate = ! $calon->wasRecentlyCreated;

        PmbImportLog::create([
            'kampus_id' => $kampus->id,
            'no_pendaftar' => $calon->no_pendaftar,
            'nama' => $calon->nama,
            'aksi' => $isDuplicate ? PmbImportLog::AKSI_DUPLICATE : PmbImportLog::AKSI_CREATED,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => $isDuplicate ? 'duplicate' : 'success',
            'data' => $calon->only(['no_pendaftar', 'nama', 'status']),
        ], $isDuplicate ? 200 : 201);
    }
}

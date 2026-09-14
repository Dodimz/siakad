<?php

namespace App\Services;

use App\Models\Fakultas;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\Prodi;
use App\Models\TahunAkademik;
use Illuminate\Support\Facades\Cache;

class AkademikService
{
    // Cache TTL in seconds (1 hour)
    protected const CACHE_TTL = 3600;

    // --- Fakultas ---
    public function getAllFakultas()
    {
        return Cache::remember(tenant_cache_key('master.fakultas'), self::CACHE_TTL, function () {
            return Fakultas::all();
        });
    }

    public function createFakultas($data)
    {
        Cache::forget(tenant_cache_key('master.fakultas'));

        return Fakultas::create($data);
    }

    // --- Prodi ---
    public function getAllProdi()
    {
        return Cache::remember(tenant_cache_key('master.prodi'), self::CACHE_TTL, function () {
            return Prodi::with('fakultas')->get();
        });
    }

    public function createProdi($data)
    {
        Cache::forget(tenant_cache_key('master.prodi'));

        return Prodi::create($data);
    }

    // --- Mata Kuliah ---
    public function getAllMataKuliah()
    {
        return Cache::remember(tenant_cache_key('master.mata_kuliah'), self::CACHE_TTL, function () {
            return MataKuliah::all();
        });
    }

    public function createMataKuliah($data)
    {
        Cache::forget(tenant_cache_key('master.mata_kuliah'));

        return MataKuliah::create($data);
    }

    // --- Tahun Akademik ---
    public function getActiveTahun()
    {
        return Cache::remember(tenant_cache_key('master.tahun_aktif'), self::CACHE_TTL, function () {
            return TahunAkademik::where('is_active', true)->first();
        });
    }

    public function activateTahun($id)
    {
        TahunAkademik::query()->update(['is_active' => false]); // Deactivate all
        $result = TahunAkademik::where('id', $id)->update(['is_active' => true]);
        Cache::forget(tenant_cache_key('master.tahun_aktif'));

        return $result;
    }

    // --- Kelas ---
    public function createKelas($data)
    {
        $data['kapasitas'] = $data['kapasitas'] ?? config('siakad.kelas_kapasitas_default');

        // Auto-assign active tahun akademik if not specified
        if (! isset($data['tahun_akademik_id'])) {
            $activeTA = TahunAkademik::where('is_active', true)->first();
            $data['tahun_akademik_id'] = $activeTA?->id;
        }

        return Kelas::create($data);
    }

    /**
     * Clear all master data caches
     */
    public function clearAllCache(): void
    {
        Cache::forget(tenant_cache_key('master.fakultas'));
        Cache::forget(tenant_cache_key('master.prodi'));
        Cache::forget(tenant_cache_key('master.mata_kuliah'));
        Cache::forget(tenant_cache_key('master.tahun_aktif'));
    }
}

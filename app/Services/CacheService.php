<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\Fakultas;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Prodi;
use App\Models\TahunAkademik;
use Illuminate\Support\Facades\Cache;

/**
 * Centralized Cache Service for SIAKAD
 *
 * Provides unified caching for frequently accessed data with automatic
 * invalidation and cache warming capabilities.
 */
class CacheService
{
    // Cache TTL constants (in seconds)
    public const TTL_SHORT = 300;      // 5 minutes - for volatile data

    public const TTL_MEDIUM = 3600;    // 1 hour - for master data

    public const TTL_LONG = 86400;     // 24 hours - for rarely changing data

    // Cache key prefixes
    private const PREFIX_MASTER = 'master';

    private const PREFIX_USER = 'user';

    private const PREFIX_STATS = 'stats';

    /**
     * ==========================================
     * MASTER DATA CACHING
     * ==========================================
     */

    /**
     * Get cached Tahun Akademik Aktif
     */
    public function getActiveTahunAkademik(): ?TahunAkademik
    {
        return Cache::remember(
            tenant_cache_key(self::PREFIX_MASTER.'.tahun_aktif'),
            self::TTL_MEDIUM,
            fn () => TahunAkademik::where('is_active', true)->first()
        );
    }

    /**
     * Get cached Fakultas list
     */
    public function getFakultasList()
    {
        return Cache::remember(
            tenant_cache_key(self::PREFIX_MASTER.'.fakultas'),
            self::TTL_MEDIUM,
            fn () => Fakultas::orderBy('nama')->get()
        );
    }

    /**
     * Get cached Prodi list with fakultas
     */
    public function getProdiList()
    {
        return Cache::remember(
            tenant_cache_key(self::PREFIX_MASTER.'.prodi'),
            self::TTL_MEDIUM,
            fn () => Prodi::with('fakultas')->orderBy('nama')->get()
        );
    }

    /**
     * Get cached Prodi by Fakultas
     */
    public function getProdiByFakultas(int $fakultasId)
    {
        return Cache::remember(
            tenant_cache_key(self::PREFIX_MASTER.".prodi.fakultas.{$fakultasId}"),
            self::TTL_MEDIUM,
            fn () => Prodi::where('fakultas_id', $fakultasId)->orderBy('nama')->get()
        );
    }

    /**
     * Get cached Mata Kuliah list
     */
    public function getMataKuliahList()
    {
        return Cache::remember(
            tenant_cache_key(self::PREFIX_MASTER.'.mata_kuliah'),
            self::TTL_MEDIUM,
            fn () => MataKuliah::with('prodi')->orderBy('kode_mk')->get()
        );
    }

    /**
     * Get cached Dosen list
     */
    public function getDosenList()
    {
        return Cache::remember(
            tenant_cache_key(self::PREFIX_MASTER.'.dosen'),
            self::TTL_MEDIUM,
            fn () => Dosen::with(['user', 'prodi'])->get()
        );
    }

    /**
     * Get cached Dosen list by Fakultas
     */
    public function getDosenByFakultas(int $fakultasId)
    {
        return Cache::remember(
            tenant_cache_key(self::PREFIX_MASTER.".dosen.fakultas.{$fakultasId}"),
            self::TTL_MEDIUM,
            fn () => Dosen::with('user')
                ->whereHas('prodi', fn ($q) => $q->where('fakultas_id', $fakultasId))
                ->get()
        );
    }

    /**
     * ==========================================
     * CACHE INVALIDATION
     * ==========================================
     */

    /**
     * Clear Tahun Akademik cache (call when activating new semester)
     */
    public function clearTahunAkademikCache(): void
    {
        Cache::forget(tenant_cache_key(self::PREFIX_MASTER.'.tahun_aktif'));
    }

    /**
     * Clear Fakultas related caches
     */
    public function clearFakultasCache(): void
    {
        Cache::forget(tenant_cache_key(self::PREFIX_MASTER.'.fakultas'));
    }

    /**
     * Clear Prodi related caches
     */
    public function clearProdiCache(?int $fakultasId = null): void
    {
        Cache::forget(tenant_cache_key(self::PREFIX_MASTER.'.prodi'));

        if ($fakultasId) {
            Cache::forget(tenant_cache_key(self::PREFIX_MASTER.".prodi.fakultas.{$fakultasId}"));
        }
    }

    /**
     * Clear Mata Kuliah cache
     */
    public function clearMataKuliahCache(): void
    {
        Cache::forget(tenant_cache_key(self::PREFIX_MASTER.'.mata_kuliah'));
    }

    /**
     * Clear Dosen cache
     */
    public function clearDosenCache(?int $fakultasId = null): void
    {
        Cache::forget(tenant_cache_key(self::PREFIX_MASTER.'.dosen'));

        if ($fakultasId) {
            Cache::forget(tenant_cache_key(self::PREFIX_MASTER.".dosen.fakultas.{$fakultasId}"));
        }
    }

    /**
     * Clear ALL master data caches
     */
    public function clearAllMasterCache(): void
    {
        $this->clearTahunAkademikCache();
        $this->clearFakultasCache();
        $this->clearProdiCache();
        $this->clearMataKuliahCache();
        $this->clearDosenCache();
    }

    /**
     * ==========================================
     * CACHE WARMING
     * ==========================================
     */

    /**
     * Warm up all frequently used caches
     * Call this after deployment or cache clear
     */
    public function warmUp(): array
    {
        $warmed = [];

        $this->getActiveTahunAkademik();
        $warmed[] = 'tahun_aktif';

        $this->getFakultasList();
        $warmed[] = 'fakultas';

        $this->getProdiList();
        $warmed[] = 'prodi';

        $this->getMataKuliahList();
        $warmed[] = 'mata_kuliah';

        $this->getDosenList();
        $warmed[] = 'dosen';

        return $warmed;
    }

    /**
     * ==========================================
     * USER-SPECIFIC CACHING
     * ==========================================
     */

    /**
     * Cache user's mahasiswa data
     */
    public function getMahasiswaByUserId(int $userId)
    {
        return Cache::remember(
            tenant_cache_key(self::PREFIX_USER.".mahasiswa.{$userId}"),
            self::TTL_SHORT,
            fn () => Mahasiswa::where('user_id', $userId)->with('prodi')->first()
        );
    }

    /**
     * Cache user's dosen data
     */
    public function getDosenByUserId(int $userId)
    {
        return Cache::remember(
            tenant_cache_key(self::PREFIX_USER.".dosen.{$userId}"),
            self::TTL_SHORT,
            fn () => Dosen::where('user_id', $userId)->with('prodi')->first()
        );
    }

    /**
     * Clear user-specific cache
     */
    public function clearUserCache(int $userId): void
    {
        Cache::forget(tenant_cache_key(self::PREFIX_USER.".mahasiswa.{$userId}"));
        Cache::forget(tenant_cache_key(self::PREFIX_USER.".dosen.{$userId}"));
    }

    /**
     * ==========================================
     * STATISTICS CACHING
     * ==========================================
     */

    /**
     * Get cached dashboard statistics
     */
    public function getDashboardStats(?int $fakultasId = null): array
    {
        $key = $fakultasId
            ? tenant_cache_key(self::PREFIX_STATS.".dashboard.fakultas.{$fakultasId}")
            : tenant_cache_key(self::PREFIX_STATS.'.dashboard.global');

        return Cache::remember($key, self::TTL_SHORT, function () use ($fakultasId) {
            $mahasiswaQuery = Mahasiswa::query();
            $dosenQuery = Dosen::query();
            $prodiQuery = Prodi::query();

            if ($fakultasId) {
                $mahasiswaQuery->whereHas('prodi', fn ($q) => $q->where('fakultas_id', $fakultasId));
                $dosenQuery->whereHas('prodi', fn ($q) => $q->where('fakultas_id', $fakultasId));
                $prodiQuery->where('fakultas_id', $fakultasId);
            }

            return [
                'total_mahasiswa' => $mahasiswaQuery->count(),
                'total_dosen' => $dosenQuery->count(),
                'total_prodi' => $prodiQuery->count(),
                'total_mata_kuliah' => MataKuliah::count(),
            ];
        });
    }

    /**
     * Clear dashboard stats cache
     */
    public function clearDashboardStats(?int $fakultasId = null): void
    {
        if ($fakultasId) {
            Cache::forget(tenant_cache_key(self::PREFIX_STATS.".dashboard.fakultas.{$fakultasId}"));
        }
        Cache::forget(tenant_cache_key(self::PREFIX_STATS.'.dashboard.global'));
    }
}

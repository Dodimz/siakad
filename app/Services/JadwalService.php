<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\JadwalKuliah;
use App\Models\Mahasiswa;
use Illuminate\Support\Collection;

class JadwalService
{
    /**
     * Check for schedule conflicts for a dosen
     */
    public function checkDosenConflict(Dosen $dosen, string $hari, string $jamMulai, string $jamSelesai, ?int $excludeId = null): ?JadwalKuliah
    {
        $kelasIds = $dosen->kelas()->pluck('id');

        return JadwalKuliah::whereIn('kelas_id', $kelasIds)
            ->where('hari', $hari)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->get()
            ->first(function ($jadwal) use ($jamMulai, $jamSelesai) {
                return $this->timeOverlaps($jadwal->jam_mulai, $jadwal->jam_selesai, $jamMulai, $jamSelesai);
            });
    }

    /**
     * Check for room conflicts
     */
    public function checkRoomConflict(string $ruangan, string $hari, string $jamMulai, string $jamSelesai, ?int $excludeId = null): ?JadwalKuliah
    {
        return JadwalKuliah::where('ruangan', $ruangan)
            ->where('hari', $hari)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->get()
            ->first(function ($jadwal) use ($jamMulai, $jamSelesai) {
                return $this->timeOverlaps($jadwal->jam_mulai, $jadwal->jam_selesai, $jamMulai, $jamSelesai);
            });
    }

    /**
     * Get weekly schedule for a mahasiswa
     */
    public function getMahasiswaSchedule(Mahasiswa $mahasiswa): Collection
    {
        $krsDetail = $mahasiswa->krs()
            ->where('status', 'approved')
            ->latest()
            ->first()
            ?->krsDetail;

        if (! $krsDetail) {
            return collect();
        }

        $kelasIds = $krsDetail->pluck('kelas_id');

        return JadwalKuliah::whereIn('kelas_id', $kelasIds)
            ->with('kelas.mataKuliah', 'kelas.dosen.user')
            ->orderByRaw("CASE hari WHEN 'Senin' THEN 0 WHEN 'Selasa' THEN 1 WHEN 'Rabu' THEN 2 WHEN 'Kamis' THEN 3 WHEN 'Jumat' THEN 4 WHEN 'Sabtu' THEN 5 ELSE 99 END")
            ->orderBy('jam_mulai')
            ->get();
    }

    /**
     * Get weekly schedule for a dosen
     */
    public function getDosenSchedule(Dosen $dosen): Collection
    {
        $kelasIds = $dosen->kelas()->pluck('id');

        return JadwalKuliah::whereIn('kelas_id', $kelasIds)
            ->with('kelas.mataKuliah')
            ->orderByRaw("CASE hari WHEN 'Senin' THEN 0 WHEN 'Selasa' THEN 1 WHEN 'Rabu' THEN 2 WHEN 'Kamis' THEN 3 WHEN 'Jumat' THEN 4 WHEN 'Sabtu' THEN 5 ELSE 99 END")
            ->orderBy('jam_mulai')
            ->get();
    }

    /**
     * Check if two time ranges overlap
     */
    private function timeOverlaps($start1, $end1, $start2, $end2): bool
    {
        return ! ($end1 <= $start2 || $start1 >= $end2);
    }
}

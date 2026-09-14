<?php

namespace App\Services;

use App\Models\CalonMahasiswa;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CalonAcceptanceService
{
    /**
     * Ubah calon mahasiswa menjadi mahasiswa aktif (buat akun + data mahasiswa).
     * Dipanggil sekali: calon berstatus imported tidak akan diproses ulang.
     */
    public function accept(CalonMahasiswa $calon, int $prodiId, ?string $emailOverride = null): Mahasiswa
    {
        return DB::transaction(function () use ($calon, $prodiId, $emailOverride) {
            if ($calon->status !== CalonMahasiswa::STATUS_PENDING) {
                throw new \RuntimeException('Calon ini sudah diproses.');
            }

            $email = trim($emailOverride ?: (string) $calon->email);

            $user = User::create([
                'kampus_id' => $calon->kampus_id,
                'name' => $calon->nama,
                'email' => $email,
                'password' => Str::random(12),
                'role' => 'mahasiswa',
            ]);

            $mahasiswa = Mahasiswa::create([
                'kampus_id' => $calon->kampus_id,
                'user_id' => $user->id,
                'nim' => $this->generateNim($calon),
                'prodi_id' => $prodiId,
                'angkatan' => $calon->tahun_masuk,
                'status' => 'aktif',
            ]);

            $calon->update(['status' => CalonMahasiswa::STATUS_IMPORTED]);

            return $mahasiswa;
        });
    }

    public function reject(CalonMahasiswa $calon): void
    {
        if ($calon->status !== CalonMahasiswa::STATUS_PENDING) {
            throw new \RuntimeException('Calon ini sudah diproses.');
        }

        $calon->update(['status' => CalonMahasiswa::STATUS_REJECTED]);
    }

    /**
     * NIM: {KODE KAMPUS}{ANGKATAN}{urutan 5 digit}, unik global via kode kampus.
     */
    protected function generateNim(CalonMahasiswa $calon): string
    {
        $angkatan = preg_replace('/\D+/', '', (string) $calon->tahun_masuk);
        $kode = strtoupper(preg_replace('/[^a-z0-9]/i', '', (string) ($calon->kampus->kode ?? ''))) ?: 'K';

        $prefix = $kode.$angkatan;

        $max = Mahasiswa::forKampus($calon->kampus)
            ->where('nim', 'like', $prefix.'%')
            ->get()
            ->reduce(fn (int $carry, Mahasiswa $m): int => max($carry, (int) substr($m->nim, strlen($prefix))), 0);

        return $prefix.str_pad((string) ($max + 1), 5, '0', STR_PAD_LEFT);
    }
}

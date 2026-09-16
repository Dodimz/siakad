<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function createUser(array $data)
    {
        return DB::transaction(function () use ($data) {
            $attributes = [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
                'fakultas_id' => $data['fakultas_id'] ?? null,
            ];

            // Hanya ditetapkan bila dikirim (mis. superadmin menunjuk kampus
            // tujuan untuk role admin_kampus); untuk role lain kampus_id akan
            // terisi otomatis dari konteks tenant via trait BelongsToTenant.
            if (array_key_exists('kampus_id', $data) && $data['kampus_id'] !== null) {
                $attributes['kampus_id'] = $data['kampus_id'];
            }

            $user = User::create($attributes);

            if ($data['role'] === 'mahasiswa') {
                Mahasiswa::create([
                    'user_id' => $user->id,
                    'nim' => $data['nim'],
                    'prodi_id' => $data['prodi_id'],
                    'angkatan' => $data['angkatan'],
                ]);
            } elseif ($data['role'] === 'dosen') {
                Dosen::create([
                    'user_id' => $user->id,
                    'nidn' => $data['nidn'],
                    'prodi_id' => $data['prodi_id'],
                ]);
            }

            return $user;
        });
    }
}

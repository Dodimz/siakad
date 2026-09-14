<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'dosen';

    protected $fillable = [
        'kampus_id',
        'user_id',
        'nidn',
        'prodi_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    public function kelas()
    {
        return $this->hasMany(Kelas::class);
    }

    /**
     * Mahasiswa yang dibimbing (sebagai Dosen PA)
     */
    public function mahasiswaBimbingan()
    {
        return $this->hasMany(Mahasiswa::class, 'dosen_pa_id');
    }

    /**
     * Skripsi yang dibimbing (sebagai pembimbing 1 atau 2)
     */
    public function skripsiPembimbing1()
    {
        return $this->hasMany(Skripsi::class, 'pembimbing1_id');
    }

    public function skripsiPembimbing2()
    {
        return $this->hasMany(Skripsi::class, 'pembimbing2_id');
    }

    /**
     * KP yang dibimbing
     */
    public function kerjaPraktek()
    {
        return $this->hasMany(KerjaPraktek::class, 'pembimbing_id');
    }

    /**
     * Kehadiran dosen
     */
    public function kehadiran()
    {
        return $this->hasMany(KehadiranDosen::class);
    }
}

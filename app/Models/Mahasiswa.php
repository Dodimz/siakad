<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'mahasiswa';

    protected $fillable = [
        'kampus_id',
        'user_id',
        'nim',
        'prodi_id',
        'dosen_pa_id',
        'angkatan',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    public function dosenPa()
    {
        return $this->belongsTo(Dosen::class, 'dosen_pa_id');
    }

    public function krs()
    {
        return $this->hasMany(Krs::class);
    }

    public function nilai()
    {
        return $this->hasMany(Nilai::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'aktif';
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function presensi()
    {
        return $this->hasMany(Presensi::class);
    }

    public function tugasSubmissions()
    {
        return $this->hasMany(TugasSubmission::class);
    }
}

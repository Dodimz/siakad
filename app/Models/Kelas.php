<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'kampus_id',
        'mata_kuliah_id',
        'dosen_id',
        'tahun_akademik_id',
        'nama_kelas',
        'kapasitas',
        'is_closed',
    ];

    protected $casts = [
        'is_closed' => 'boolean',
    ];

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class);
    }

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class);
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class);
    }

    public function krsDetail()
    {
        return $this->hasMany(KrsDetail::class);
    }

    public function nilai()
    {
        return $this->hasMany(Nilai::class);
    }

    public function jadwal()
    {
        return $this->hasMany(JadwalKuliah::class);
    }

    public function isFull(): bool
    {
        return $this->krsDetail()->count() >= $this->kapasitas;
    }

    public function pertemuan()
    {
        return $this->hasMany(Pertemuan::class);
    }

    public function tugas()
    {
        return $this->hasMany(Tugas::class);
    }
}

<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ruangan extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'ruangan';

    protected $fillable = [
        'kampus_id',
        'kode_ruangan',
        'nama_ruangan',
        'kapasitas',
        'gedung',
        'lantai',
        'fasilitas',
        'is_active',
        'fakultas_id',
    ];

    protected $casts = [
        'kapasitas' => 'integer',
        'lantai' => 'integer',
        'is_active' => 'boolean',
    ];

    public function fakultas()
    {
        return $this->belongsTo(Fakultas::class);
    }

    /**
     * Jadwal yang menggunakan ruangan ini
     */
    public function jadwalKuliah()
    {
        return $this->hasMany(JadwalKuliah::class, 'ruangan_id');
    }

    /**
     * Scope untuk ruangan aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataKuliah extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'mata_kuliah';

    protected $fillable = [
        'kampus_id',
        'kode_mk',
        'nama_mk',
        'sks',
        'semester',
        'prodi_id',
    ];

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    public function kelas()
    {
        return $this->hasMany(Kelas::class);
    }
}

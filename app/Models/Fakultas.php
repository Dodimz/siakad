<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fakultas extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'fakultas';

    protected $fillable = [
        'kampus_id',
        'nama',
    ];

    public function prodi()
    {
        return $this->hasMany(Prodi::class);
    }

    public function ruangan()
    {
        return $this->hasMany(Ruangan::class);
    }
}

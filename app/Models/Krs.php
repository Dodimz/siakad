<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Krs extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'krs';

    protected $fillable = [
        'kampus_id',
        'mahasiswa_id',
        'tahun_akademik_id',
        'status',
        'catatan',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class);
    }

    public function krsDetail()
    {
        return $this->hasMany(KrsDetail::class);
    }
}

<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KrsDetail extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'krs_detail';

    protected $fillable = [
        'kampus_id',
        'krs_id',
        'kelas_id',
    ];

    public function krs()
    {
        return $this->belongsTo(Krs::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }
}

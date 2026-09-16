<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JadwalKuliah extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'jadwal_kuliah';

    protected $fillable = [
        'kampus_id',
        'kelas_id',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'ruangan',
    ];

    protected $casts = [
        'jam_mulai' => 'datetime:H:i',
        'jam_selesai' => 'datetime:H:i',
    ];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    /**
     * Check if this schedule conflicts with another
     */
    public function conflictsWith(JadwalKuliah $other): bool
    {
        if ($this->hari !== $other->hari) {
            return false;
        }

        return ! ($this->jam_selesai <= $other->jam_mulai || $this->jam_mulai >= $other->jam_selesai);
    }

    /**
     * Get all pertemuan (meetings) for this schedule
     */
    public function pertemuan(): HasMany
    {
        return $this->hasMany(Pertemuan::class);
    }
}

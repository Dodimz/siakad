<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Presensi extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'presensi';

    const STATUS_HADIR = 'hadir';

    const STATUS_SAKIT = 'sakit';

    const STATUS_IZIN = 'izin';

    const STATUS_ALPA = 'alpa';

    protected $fillable = [
        'kampus_id',
        'pertemuan_id',
        'mahasiswa_id',
        'status',
        'waktu_presensi',
        'keterangan',
    ];

    protected $casts = [
        'waktu_presensi' => 'datetime:H:i',
    ];

    public function pertemuan(): BelongsTo
    {
        return $this->belongsTo(Pertemuan::class);
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    /**
     * Check if this counts as attendance (hadir or izin/sakit with valid reason)
     */
    public function isPresent(): bool
    {
        return $this->status === self::STATUS_HADIR;
    }

    /**
     * Check if this counts as valid absence (izin or sakit)
     */
    public function isValidAbsence(): bool
    {
        return in_array($this->status, [self::STATUS_IZIN, self::STATUS_SAKIT]);
    }

    /**
     * Get status badge color
     */
    public function getStatusColor(): string
    {
        return match ($this->status) {
            self::STATUS_HADIR => 'emerald',
            self::STATUS_SAKIT => 'amber',
            self::STATUS_IZIN => 'blue',
            self::STATUS_ALPA => 'red',
            default => 'gray',
        };
    }

    /**
     * Get status label in Indonesian
     */
    public function getStatusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_HADIR => 'Hadir',
            self::STATUS_SAKIT => 'Sakit',
            self::STATUS_IZIN => 'Izin',
            self::STATUS_ALPA => 'Alpa',
            default => '-',
        };
    }
}

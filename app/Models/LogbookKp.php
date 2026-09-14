<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogbookKp extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'logbook_kp';

    protected $fillable = [
        'kampus_id',
        'kerja_praktek_id',
        'tanggal',
        'jam_masuk',
        'jam_keluar',
        'kegiatan',
        'catatan_pembimbing',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    const STATUS_PENDING = 'pending';

    const STATUS_DISETUJUI = 'disetujui';

    const STATUS_REVISI = 'revisi';

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'amber',
            self::STATUS_DISETUJUI => 'emerald',
            self::STATUS_REVISI => 'red',
            default => 'slate'
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Menunggu',
            self::STATUS_DISETUJUI => 'Disetujui',
            self::STATUS_REVISI => 'Revisi',
            default => $this->status
        };
    }

    public function kerjaPraktek()
    {
        return $this->belongsTo(KerjaPraktek::class);
    }
}

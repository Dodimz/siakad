<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BimbinganSkripsi extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'bimbingan_skripsi';

    protected $fillable = [
        'kampus_id',
        'skripsi_id',
        'dosen_id',
        'tanggal_bimbingan',
        'catatan_mahasiswa',
        'catatan_dosen',
        'file_dokumen',
        'status',
    ];

    protected $casts = [
        'tanggal_bimbingan' => 'date',
    ];

    const STATUS_MENUNGGU = 'menunggu';

    const STATUS_DISETUJUI = 'disetujui';

    const STATUS_REVISI = 'revisi';

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU => 'amber',
            self::STATUS_DISETUJUI => 'emerald',
            self::STATUS_REVISI => 'red',
            default => 'slate'
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_MENUNGGU => 'Menunggu Review',
            self::STATUS_DISETUJUI => 'Disetujui',
            self::STATUS_REVISI => 'Perlu Revisi',
            default => $this->status
        };
    }

    // Relationships
    public function skripsi()
    {
        return $this->belongsTo(Skripsi::class);
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class);
    }
}

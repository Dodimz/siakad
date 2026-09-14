<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalonMahasiswa extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'calon_mahasiswa';

    protected $attributes = [
        'status' => self::STATUS_PENDING,
    ];

    protected $fillable = [
        'kampus_id',
        'no_pendaftar',
        'nama',
        'email',
        'telepon',
        'jalur',
        'tahun_masuk',
        'prodi_nama',
        'payload',
        'status',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public const STATUS_PENDING = 'pending';

    public const STATUS_IMPORTED = 'imported';

    public const STATUS_REJECTED = 'rejected';

    public static function getStatusList(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IMPORTED => 'Terima',
            self::STATUS_REJECTED => 'Tolak',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatusList()[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'amber',
            self::STATUS_IMPORTED => 'emerald',
            self::STATUS_REJECTED => 'red',
            default => 'slate',
        };
    }

    public function kampus(): BelongsTo
    {
        return $this->belongsTo(Kampus::class);
    }

    public function markAsImported(): void
    {
        $this->update(['status' => self::STATUS_IMPORTED]);
    }
}

<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PmbImportLog extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'kampus_id',
        'no_pendaftar',
        'nama',
        'aksi',
        'ip_address',
    ];

    public const AKSI_CREATED = 'created';

    public const AKSI_DUPLICATE = 'duplicate';

    public const AKSI_LIST = [
        self::AKSI_CREATED => 'Import Baru',
        self::AKSI_DUPLICATE => 'Duplikat',
    ];

    public function getAksiLabelAttribute(): string
    {
        return self::AKSI_LIST[$this->aksi] ?? $this->aksi;
    }

    public function getAksiColorAttribute(): string
    {
        return $this->aksi === self::AKSI_CREATED ? 'emerald' : 'amber';
    }

    public function kampus(): BelongsTo
    {
        return $this->belongsTo(Kampus::class);
    }
}

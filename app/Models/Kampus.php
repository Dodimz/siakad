<?php

namespace App\Models;

use Database\Factories\KampusFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Kampus extends Model
{
    /** @use HasFactory<KampusFactory> */
    use HasFactory;

    protected $table = 'kampus';

    protected $fillable = [
        'nama',
        'slug',
        'kode',
        'alamat',
        'telepon',
        'email',
        'logo',
        'status',
        'settings',
    ];

    protected $hidden = [
        'pmb_token',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public const STATUS_TRIAL = 'trial';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_SUSPENDED = 'suspended';

    public static function getStatusList(): array
    {
        return [
            self::STATUS_TRIAL => 'Trial',
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_SUSPENDED => 'Suspend',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatusList()[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'emerald',
            self::STATUS_TRIAL => 'amber',
            self::STATUS_SUSPENDED => 'red',
            default => 'slate',
        };
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Terbitkan token PMB baru. Token lama langsung tidak berlaku.
     */
    public function issuePmbToken(bool $force = false): string
    {
        if (! $force && $this->pmb_token) {
            return $this->pmb_token;
        }

        $this->pmb_token = 'pmb_'.Str::random(64);

        $this->save();

        return $this->pmb_token;
    }

    public function hasPmbToken(): bool
    {
        return (bool) $this->pmb_token;
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function fakultas()
    {
        return $this->hasMany(Fakultas::class);
    }

    public function prodi()
    {
        return $this->hasMany(Prodi::class);
    }

    public function mahasiswa()
    {
        return $this->hasMany(Mahasiswa::class);
    }

    public function dosen()
    {
        return $this->hasMany(Dosen::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function calonMahasiswa()
    {
        return $this->hasMany(CalonMahasiswa::class);
    }
}

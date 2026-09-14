<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TahunAkademik extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'tahun_akademik';

    protected $fillable = [
        'kampus_id',
        'tahun',
        'semester',
        'is_active',
        'tanggal_mulai',
        'tanggal_selesai',
        'tanggal_krs_mulai',
        'tanggal_krs_selesai',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'tanggal_krs_mulai' => 'date',
        'tanggal_krs_selesai' => 'date',
    ];

    public function krs()
    {
        return $this->hasMany(Krs::class);
    }

    public function kelas()
    {
        return $this->hasMany(Kelas::class);
    }

    /**
     * Get the active tahun akademik
     */
    public static function active()
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Check if semester is currently running
     */
    public function isSemesterActive(): bool
    {
        if (! $this->tanggal_mulai || ! $this->tanggal_selesai) {
            return $this->is_active;
        }

        $today = now()->toDateString();

        return $today >= $this->tanggal_mulai->toDateString()
            && $today <= $this->tanggal_selesai->toDateString();
    }

    /**
     * Check if KRS period is open
     */
    public function isKrsPeriod(): bool
    {
        if (! $this->tanggal_krs_mulai || ! $this->tanggal_krs_selesai) {
            return $this->is_active;
        }

        $today = now()->toDateString();

        return $today >= $this->tanggal_krs_mulai->toDateString()
            && $today <= $this->tanggal_krs_selesai->toDateString();
    }

    /**
     * Get display name (e.g., "2024/2025 Ganjil")
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->tahun} {$this->semester}";
    }
}

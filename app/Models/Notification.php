<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'kampus_id',
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    // Notification Types
    const TYPE_JADWAL_CHANGE = 'jadwal_change';

    const TYPE_KRS_APPROVED = 'krs_approved';

    const TYPE_KRS_REJECTED = 'krs_rejected';

    const TYPE_NILAI_UPDATED = 'nilai_updated';

    const TYPE_PRESENSI_WARNING = 'presensi_warning';

    /**
     * User pemilik notifikasi
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope untuk notifikasi yang belum dibaca
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope untuk notifikasi yang sudah dibaca
     */
    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    /**
     * Cek apakah sudah dibaca
     */
    public function isRead(): bool
    {
        return ! is_null($this->read_at);
    }

    /**
     * Tandai sebagai sudah dibaca
     */
    public function markAsRead(): void
    {
        if (! $this->isRead()) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Get icon berdasarkan type
     */
    public function getIconAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_JADWAL_CHANGE => '📅',
            self::TYPE_KRS_APPROVED => '✅',
            self::TYPE_KRS_REJECTED => '❌',
            self::TYPE_NILAI_UPDATED => '📊',
            self::TYPE_PRESENSI_WARNING => '⚠️',
            default => '🔔'
        };
    }

    /**
     * Get color berdasarkan type
     */
    public function getColorAttribute(): string
    {
        return match ($this->type) {
            self::TYPE_JADWAL_CHANGE => 'blue',
            self::TYPE_KRS_APPROVED => 'emerald',
            self::TYPE_KRS_REJECTED => 'red',
            self::TYPE_NILAI_UPDATED => 'purple',
            self::TYPE_PRESENSI_WARNING => 'amber',
            default => 'slate'
        };
    }
}

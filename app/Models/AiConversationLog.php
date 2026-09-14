<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiConversationLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'kampus_id',
        'user_id',
        'mahasiswa_id',
        'session_id',
        'question',
        'answer',
        'context_summary',
        'response_time_ms',
        'model_used',
        'provider',
        'guard_applied',
        'guard_issues',
        'was_retry',
        'feedback',
        'feedback_note',
    ];

    protected $casts = [
        'guard_applied' => 'boolean',
        'guard_issues' => 'array',
        'was_retry' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    /**
     * Scope for filtering by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get average response time
     */
    public static function averageResponseTime(): float
    {
        return static::avg('response_time_ms') ?? 0;
    }

    /**
     * Get guard trigger rate (percentage)
     */
    public static function guardTriggerRate(): float
    {
        $total = static::count();
        if ($total === 0) {
            return 0;
        }

        $triggered = static::where('guard_applied', true)->count();

        return round(($triggered / $total) * 100, 2);
    }
}

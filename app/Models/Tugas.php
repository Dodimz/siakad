<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'tugas';

    protected $fillable = [
        'kampus_id',
        'kelas_id',
        'judul',
        'deskripsi',
        'file_tugas',
        'deadline',
        'max_file_size',
        'allowed_extensions',
        'is_active',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'is_active' => 'boolean',
        'max_file_size' => 'integer',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function submissions()
    {
        return $this->hasMany(TugasSubmission::class);
    }

    /**
     * Check if deadline has passed
     */
    public function isOverdue(): bool
    {
        return Carbon::now()->isAfter($this->deadline);
    }

    /**
     * Check if still accepting submissions
     */
    public function isOpen(): bool
    {
        return $this->is_active && ! $this->isOverdue();
    }

    /**
     * Get remaining time until deadline
     */
    public function getRemainingTimeAttribute(): string
    {
        if ($this->isOverdue()) {
            return 'Sudah lewat';
        }

        return $this->deadline->diffForHumans(['parts' => 2]);
    }

    /**
     * Get submission count
     */
    public function getSubmissionCountAttribute(): int
    {
        return $this->submissions()->count();
    }

    /**
     * Get graded count
     */
    public function getGradedCountAttribute(): int
    {
        return $this->submissions()->whereNotNull('nilai')->count();
    }

    /**
     * Get allowed extensions as array
     */
    public function getAllowedExtensionsArrayAttribute(): array
    {
        return array_map('trim', explode(',', $this->allowed_extensions));
    }

    /**
     * Get formatted max file size
     */
    public function getFormattedMaxFileSizeAttribute(): string
    {
        $bytes = $this->max_file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 1).' '.$units[$i];
    }
}

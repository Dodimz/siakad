<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TugasSubmission extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'tugas_submission';

    protected $fillable = [
        'kampus_id',
        'tugas_id',
        'mahasiswa_id',
        'file_path',
        'file_name',
        'catatan',
        'submitted_at',
        'nilai',
        'feedback',
        'graded_at',
        'graded_by',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
        'nilai' => 'decimal:2',
    ];

    public function tugas()
    {
        return $this->belongsTo(Tugas::class);
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function gradedBy()
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Check if submission is graded
     */
    public function isGraded(): bool
    {
        return $this->nilai !== null;
    }

    /**
     * Check if submitted on time
     */
    public function isOnTime(): bool
    {
        return $this->submitted_at->isBefore($this->tugas->deadline);
    }

    /**
     * Get grade letter (A, B, C, D, E)
     */
    public function getGradeLetterAttribute(): ?string
    {
        if ($this->nilai === null) {
            return null;
        }

        return match (true) {
            $this->nilai >= 85 => 'A',
            $this->nilai >= 70 => 'B',
            $this->nilai >= 55 => 'C',
            $this->nilai >= 40 => 'D',
            default => 'E',
        };
    }
}

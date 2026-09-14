<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materi extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'materi';

    protected $fillable = [
        'kampus_id',
        'pertemuan_id',
        'judul',
        'deskripsi',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
        'link_external',
        'urutan',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'urutan' => 'integer',
    ];

    public function pertemuan()
    {
        return $this->belongsTo(Pertemuan::class);
    }

    /**
     * Get formatted file size
     */
    public function getFormattedFileSizeAttribute(): string
    {
        if (! $this->file_size) {
            return '-';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    /**
     * Check if materi is a file (not external link)
     */
    public function isFile(): bool
    {
        return ! empty($this->file_path);
    }

    /**
     * Check if materi is external link
     */
    public function isExternalLink(): bool
    {
        return ! empty($this->link_external);
    }

    /**
     * Get file icon based on type
     */
    public function getFileIconAttribute(): string
    {
        $type = $this->file_type ?? '';

        return match (true) {
            str_contains($type, 'pdf') => 'pdf',
            str_contains($type, 'word') || str_contains($type, 'doc') => 'doc',
            str_contains($type, 'excel') || str_contains($type, 'sheet') => 'xls',
            str_contains($type, 'powerpoint') || str_contains($type, 'presentation') => 'ppt',
            str_contains($type, 'image') => 'image',
            str_contains($type, 'video') => 'video',
            str_contains($type, 'zip') || str_contains($type, 'rar') => 'zip',
            default => 'file',
        };
    }
}

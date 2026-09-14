<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pertemuan extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'pertemuan';

    const STATUS_TERLAKSANA = 'terlaksana';

    const STATUS_LIBUR = 'libur';

    const STATUS_PENGGANTI = 'pengganti';

    protected $fillable = [
        'kampus_id',
        'jadwal_kuliah_id',
        'pertemuan_ke',
        'tanggal',
        'materi',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function jadwalKuliah(): BelongsTo
    {
        return $this->belongsTo(JadwalKuliah::class);
    }

    public function presensi(): HasMany
    {
        return $this->hasMany(Presensi::class);
    }

    /**
     * Scope to get only completed meetings
     */
    public function scopeTerlaksana($query)
    {
        return $query->where('status', self::STATUS_TERLAKSANA);
    }

    /**
     * Scope to filter by kelas
     */
    public function scopeByKelas($query, $kelasId)
    {
        return $query->whereHas('jadwalKuliah', fn ($q) => $q->where('kelas_id', $kelasId));
    }

    /**
     * Get list of mahasiswa enrolled in this class
     */
    public function getMahasiswaList()
    {
        return Mahasiswa::whereHas('krs', function ($q) {
            $q->where('status', 'approved')
                ->whereHas('krsDetail', fn ($q2) => $q2->where('kelas_id', $this->jadwalKuliah->kelas_id));
        })->with('user')->get();
    }

    /**
     * List of materi for this pertemuan
     */
    public function materiList(): HasMany
    {
        return $this->hasMany(Materi::class)->orderBy('urutan');
    }
}

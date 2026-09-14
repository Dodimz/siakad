<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KehadiranDosen extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'kehadiran_dosen';

    protected $fillable = [
        'kampus_id',
        'dosen_id',
        'jadwal_kuliah_id',
        'pertemuan_id',
        'tanggal',
        'jam_masuk',
        'jam_keluar',
        'status',
        'keterangan',
        'bukti_file',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    const STATUS_HADIR = 'hadir';

    const STATUS_IZIN = 'izin';

    const STATUS_SAKIT = 'sakit';

    const STATUS_TUGAS = 'tugas';

    const STATUS_ALPA = 'alpa';

    public static function getStatusList(): array
    {
        return [
            self::STATUS_HADIR => 'Hadir',
            self::STATUS_IZIN => 'Izin',
            self::STATUS_SAKIT => 'Sakit',
            self::STATUS_TUGAS => 'Tugas Luar',
            self::STATUS_ALPA => 'Tidak Hadir',
        ];
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_HADIR => 'emerald',
            self::STATUS_IZIN => 'blue',
            self::STATUS_SAKIT => 'amber',
            self::STATUS_TUGAS => 'purple',
            self::STATUS_ALPA => 'red',
            default => 'slate'
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatusList()[$this->status] ?? $this->status;
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class);
    }

    public function jadwalKuliah()
    {
        return $this->belongsTo(JadwalKuliah::class);
    }

    public function pertemuan()
    {
        return $this->belongsTo(Pertemuan::class);
    }

    public function scopeByDosen($query, $dosenId)
    {
        return $query->where('dosen_id', $dosenId);
    }

    public function scopeByMonth($query, $year, $month)
    {
        return $query->whereYear('tanggal', $year)->whereMonth('tanggal', $month);
    }
}

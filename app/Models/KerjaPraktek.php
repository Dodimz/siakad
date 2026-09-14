<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KerjaPraktek extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'kerja_praktek';

    protected $fillable = [
        'kampus_id',
        'mahasiswa_id',
        'pembimbing_id',
        'nama_perusahaan',
        'alamat_perusahaan',
        'bidang_usaha',
        'nama_pembimbing_lapangan',
        'jabatan_pembimbing_lapangan',
        'no_telp_pembimbing',
        'tanggal_mulai',
        'tanggal_selesai',
        'judul_laporan',
        'status',
        'tanggal_seminar',
        'nilai_perusahaan',
        'nilai_pembimbing',
        'nilai_seminar',
        'nilai_akhir',
        'nilai_huruf',
        'catatan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'tanggal_seminar' => 'date',
        'nilai_perusahaan' => 'decimal:2',
        'nilai_pembimbing' => 'decimal:2',
        'nilai_seminar' => 'decimal:2',
        'nilai_akhir' => 'decimal:2',
    ];

    const STATUS_PENGAJUAN = 'pengajuan';

    const STATUS_DISETUJUI = 'disetujui';

    const STATUS_DITOLAK = 'ditolak';

    const STATUS_BERLANGSUNG = 'berlangsung';

    const STATUS_SELESAI_KP = 'selesai_kp';

    const STATUS_PENYUSUNAN = 'penyusunan_laporan';

    const STATUS_SEMINAR = 'seminar';

    const STATUS_REVISI = 'revisi';

    const STATUS_SELESAI = 'selesai';

    public static function getStatusList(): array
    {
        return [
            self::STATUS_PENGAJUAN => 'Pengajuan',
            self::STATUS_DISETUJUI => 'Disetujui',
            self::STATUS_DITOLAK => 'Ditolak',
            self::STATUS_BERLANGSUNG => 'Sedang Berlangsung',
            self::STATUS_SELESAI_KP => 'Selesai KP',
            self::STATUS_PENYUSUNAN => 'Penyusunan Laporan',
            self::STATUS_SEMINAR => 'Seminar',
            self::STATUS_REVISI => 'Revisi',
            self::STATUS_SELESAI => 'Selesai',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::getStatusList()[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENGAJUAN => 'amber',
            self::STATUS_DISETUJUI, self::STATUS_BERLANGSUNG => 'blue',
            self::STATUS_DITOLAK => 'red',
            self::STATUS_SELESAI_KP, self::STATUS_PENYUSUNAN => 'purple',
            self::STATUS_SEMINAR => 'indigo',
            self::STATUS_REVISI => 'orange',
            self::STATUS_SELESAI => 'emerald',
            default => 'slate'
        };
    }

    public function getProgressPercentAttribute(): int
    {
        $map = [
            self::STATUS_PENGAJUAN => 5,
            self::STATUS_DISETUJUI => 10,
            self::STATUS_DITOLAK => 0,
            self::STATUS_BERLANGSUNG => 40,
            self::STATUS_SELESAI_KP => 60,
            self::STATUS_PENYUSUNAN => 75,
            self::STATUS_SEMINAR => 90,
            self::STATUS_REVISI => 95,
            self::STATUS_SELESAI => 100,
        ];

        return $map[$this->status] ?? 0;
    }

    public function getDurasiAttribute(): string
    {
        if (! $this->tanggal_mulai || ! $this->tanggal_selesai) {
            return '-';
        }

        return $this->tanggal_mulai->diffInWeeks($this->tanggal_selesai).' minggu';
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function pembimbing()
    {
        return $this->belongsTo(Dosen::class, 'pembimbing_id');
    }

    public function logbook()
    {
        return $this->hasMany(LogbookKp::class)->orderBy('tanggal', 'desc');
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [self::STATUS_SELESAI, self::STATUS_DITOLAK]);
    }
}

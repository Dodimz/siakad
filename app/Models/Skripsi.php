<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skripsi extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'skripsi';

    protected $fillable = [
        'kampus_id',
        'mahasiswa_id',
        'pembimbing1_id',
        'pembimbing2_id',
        'judul',
        'abstrak',
        'bidang_kajian',
        'status',
        'tanggal_pengajuan',
        'tanggal_acc_judul',
        'tanggal_seminar_proposal',
        'tanggal_seminar_hasil',
        'tanggal_sidang',
        'tanggal_selesai',
        'nilai_akhir',
        'nilai_huruf',
        'catatan_admin',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'date',
        'tanggal_acc_judul' => 'date',
        'tanggal_seminar_proposal' => 'date',
        'tanggal_seminar_hasil' => 'date',
        'tanggal_sidang' => 'date',
        'tanggal_selesai' => 'date',
        'nilai_akhir' => 'decimal:2',
    ];

    // Status constants
    const STATUS_PENGAJUAN = 'pengajuan';

    const STATUS_REVIEW = 'review';

    const STATUS_DITOLAK = 'ditolak';

    const STATUS_DITERIMA = 'diterima';

    const STATUS_BIMBINGAN = 'bimbingan';

    const STATUS_SEMINAR_PROPOSAL = 'seminar_proposal';

    const STATUS_PENELITIAN = 'penelitian';

    const STATUS_SEMINAR_HASIL = 'seminar_hasil';

    const STATUS_SIDANG = 'sidang';

    const STATUS_REVISI = 'revisi';

    const STATUS_SELESAI = 'selesai';

    public static function getStatusList(): array
    {
        return [
            self::STATUS_PENGAJUAN => 'Pengajuan Judul',
            self::STATUS_REVIEW => 'Dalam Review',
            self::STATUS_DITOLAK => 'Judul Ditolak',
            self::STATUS_DITERIMA => 'Judul Diterima',
            self::STATUS_BIMBINGAN => 'Bimbingan',
            self::STATUS_SEMINAR_PROPOSAL => 'Seminar Proposal',
            self::STATUS_PENELITIAN => 'Penelitian',
            self::STATUS_SEMINAR_HASIL => 'Seminar Hasil',
            self::STATUS_SIDANG => 'Sidang Akhir',
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
            self::STATUS_PENGAJUAN, self::STATUS_REVIEW => 'amber',
            self::STATUS_DITOLAK => 'red',
            self::STATUS_DITERIMA, self::STATUS_BIMBINGAN, self::STATUS_PENELITIAN => 'blue',
            self::STATUS_SEMINAR_PROPOSAL, self::STATUS_SEMINAR_HASIL, self::STATUS_SIDANG => 'purple',
            self::STATUS_REVISI => 'orange',
            self::STATUS_SELESAI => 'emerald',
            default => 'slate'
        };
    }

    public function getProgressPercentAttribute(): int
    {
        $progressMap = [
            self::STATUS_PENGAJUAN => 5,
            self::STATUS_REVIEW => 10,
            self::STATUS_DITOLAK => 5,
            self::STATUS_DITERIMA => 15,
            self::STATUS_BIMBINGAN => 30,
            self::STATUS_SEMINAR_PROPOSAL => 45,
            self::STATUS_PENELITIAN => 60,
            self::STATUS_SEMINAR_HASIL => 75,
            self::STATUS_SIDANG => 90,
            self::STATUS_REVISI => 95,
            self::STATUS_SELESAI => 100,
        ];

        return $progressMap[$this->status] ?? 0;
    }

    // Relationships
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function pembimbing1()
    {
        return $this->belongsTo(Dosen::class, 'pembimbing1_id');
    }

    public function pembimbing2()
    {
        return $this->belongsTo(Dosen::class, 'pembimbing2_id');
    }

    public function bimbingan()
    {
        return $this->hasMany(BimbinganSkripsi::class)->orderBy('tanggal_bimbingan', 'desc');
    }

    // Scopes
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [self::STATUS_SELESAI, self::STATUS_DITOLAK]);
    }
}

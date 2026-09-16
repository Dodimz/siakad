<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use BelongsToTenant, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'kampus_id',
        'name',
        'email',
        'password',
        'role',
        'fakultas_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function mahasiswa()
    {
        return $this->hasOne(Mahasiswa::class);
    }

    public function dosen()
    {
        return $this->hasOne(Dosen::class);
    }

    public function fakultas()
    {
        return $this->belongsTo(Fakultas::class);
    }

    /**
     * Check if user is superadmin (can access all data)
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    /**
     * Check if user is admin (faculty-level or superadmin)
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['superadmin', 'admin_fakultas', 'admin_kampus']);
    }

    /**
     * Check if user can access a specific fakultas
     */
    public function canAccessFakultas(int $fakultasId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->fakultas_id === $fakultasId;
    }

    /**
     * Get fakultas IDs this user can access
     */
    public function getAccessibleFakultasIds(): array
    {
        if ($this->isSuperAdmin()) {
            return Fakultas::pluck('id')->toArray();
        }

        return $this->fakultas_id ? [$this->fakultas_id] : [];
    }
}

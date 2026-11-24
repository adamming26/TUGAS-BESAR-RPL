<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'nim_nip',
        'role',
        'password',
        'phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi ke peminjaman yang diajukan oleh pengguna ini.
     */
    public function borrowRequests(): HasMany
    {
        return $this->hasMany(BorrowRequest::class, 'user_id');
    }

    /**
     * Relasi ke peminjaman yang disetujui/ditolak oleh pengguna ini (Dosen).
     */
    public function approvedBorrowRequests(): HasMany
    {
        return $this->hasMany(BorrowRequest::class, 'approved_by');
    }

    /**
     * Relasi ke catatan pengembalian yang divalidasi oleh dosen ini.
     */
    public function verifiedReturnRecords(): HasMany
    {
        return $this->hasMany(ReturnRecord::class, 'lecturer_id');
    }

    public function getNimAttribute(): ?string
    {
        return $this->role === 'mahasiswa' ? $this->nim_nip : null;
    }

    public function getNipAttribute(): ?string
    {
        return $this->role === 'dosen' ? $this->nim_nip : null;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isDosen(): bool
    {
        return $this->role === 'dosen';
    }

    public function isMahasiswa(): bool
    {
        return $this->role === 'mahasiswa';
    }
}

<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Table('users')]
#[Fillable([
    'nim_nidn',
    'nama_lengkap',
    'role',
    'prodi',
    'fakultas',
    'password',
])]
#[Hidden([
    'password',
    'remember_token',
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Role constants for clean reference across microservices.
     */
    public const ROLE_MAHASISWA = 'mahasiswa';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_ORMAWA = 'ormawa';
    public const ROLE_DOSEN = 'dosen';

    /**
     * Helper methods to inspect user role.
     */
    public function isMahasiswa(): bool
    {
        return $this->role === self::ROLE_MAHASISWA;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isOrmawa(): bool
    {
        return $this->role === self::ROLE_ORMAWA;
    }

    public function isDosen(): bool
    {
        return $this->role === self::ROLE_DOSEN;
    }
}

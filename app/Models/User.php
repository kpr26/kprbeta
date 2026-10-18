<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'role',
        'is_active',
        'email',
        'password',
        'foto_profil',
        'tanda_tangan',
    ];

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->foto_profil ? asset('storage/' . $this->foto_profil) : null;
    }

    public function getSignatureUrlAttribute(): ?string
    {
        return $this->tanda_tangan ? asset('storage/' . $this->tanda_tangan) : null;
    }

    public function isSuperAdmin(): bool
    {
        return in_array($this->role, ['admin', 'superadmin'], true);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
            'is_active' => 'boolean',
        ];
    }
}

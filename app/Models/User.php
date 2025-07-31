<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // <-- TAMBAHKAN INI

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    // --- TAMBAHKAN TRAIT DI SINI ---
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'person_id',
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

    /**
     * Mendefinisikan relasi ke aturan akses operator.
     * Seorang User hanya memiliki satu aturan akses.
     */
    public function accessControl(): HasOne
    {
        return $this->hasOne(OperatorAccessControl::class);
    }
}
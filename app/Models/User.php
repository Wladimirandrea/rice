<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'profile_image',
        'created_by',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['profile_image_url'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // ─── Accessors ────────────────────────────────────────────
    public function getProfileImageUrlAttribute(): string
    {
        if ($this->profile_image) {
            return asset('storage/' . $this->profile_image);
        }
        // Genera avatar con iniciales como fallback
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=0D8ABC&color=fff';
    }

    // ─── Helpers de Rol ───────────────────────────────────────
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCaseManager(): bool
    {
        return $this->role === 'case_manager';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    // ─── Relaciones ───────────────────────────────────────────

    /** Admin que creó este usuario */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Usuarios creados por este admin */
    public function createdUsers(): HasMany
    {
        return $this->hasMany(User::class, 'created_by');
    }

    /** Clientes asignados a este Case Manager */
    public function clients(): HasMany
    {
        return $this->hasMany(User::class, 'created_by')
            ->where('role', 'client');
    }

    /** Case Manager asignado a este cliente */
    public function caseManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sendPasswordResetNotification($token): void
    {
        $frontendUrl = config('app.frontend_url', 'http://192.168.12.125:5173');

        ResetPasswordNotification::createUrlUsing(function ($user, $token) use ($frontendUrl) {
            return $frontendUrl . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);
        });

        $this->notify(new ResetPasswordNotification($token));
    }
}

<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_USER = 'user';

    protected $fillable = ['name', 'email', 'phone', 'password', 'role', 'is_active', 'last_login_at'];

    protected $hidden = ['password', 'remember_token'];

    protected $attributes = ['role' => self::ROLE_USER, 'is_active' => true];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /* ---------- Relasi ---------- */

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }

    public function entitlements(): HasMany
    {
        return $this->hasMany(UserTryout::class);
    }

    public function ownedTryouts(): BelongsToMany
    {
        return $this->belongsToMany(Tryout::class, 'user_tryouts')->withTimestamps();
    }

    /* ---------- Helper ---------- */

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function firstName(): string
    {
        return explode(' ', trim($this->name))[0] ?? $this->name;
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', trim($this->name));

        return strtoupper(mb_substr($parts[0] ?? 'U', 0, 1).mb_substr($parts[1] ?? '', 0, 1));
    }

    /** ID tryout yang boleh diakses (dibeli/diberikan). Di-memo per request. */
    public function ownedTryoutIds(): Collection
    {
        return once(fn () => $this->entitlements()->pluck('tryout_id'));
    }

    public function canAccessTryout(Tryout $tryout): bool
    {
        return $this->isAdmin() || $tryout->is_free || $this->ownedTryoutIds()->contains($tryout->id);
    }

    public function activeAttempt(): ?Attempt
    {
        return $this->attempts()
            ->where('status', Attempt::STATUS_IN_PROGRESS)
            ->where('deadline_at', '>', now())
            ->with('tryout:id,title,slug,category_id')
            ->latest('started_at')
            ->first();
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new \App\Notifications\VerifyEmailNotification);
    }

    /** Nomor WA dalam format internasional (62xxxx). */
    public function whatsappNumber(): ?string
    {
        return $this->phone ? normalize_phone($this->phone) : null;
    }
}

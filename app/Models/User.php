<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\KycVerificationStatus;
use App\Enums\UserStatus;
use App\Traits\HasUuid;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['first_name', 'last_name', 'email', 'password', 'country_code', 'phone', 'status'])]
#[Hidden(['password', 'remember_token'])]
final class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, HasUuid, InteractsWithMedia, Notifiable;

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getIsEmailVerifiedAttribute(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function markEmailAsVerified(): bool
    {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
        ])->save();
    }

    public function getIsKycApprovedAttribute(): bool
    {
        return $this->kycVerification?->status === KycVerificationStatus::APPROVED->value;
    }

    public function otpCodes(): HasMany
    {
        return $this->hasMany(OtpCode::class);
    }

    public function kycVerification(): HasOne
    {
        return $this->hasOne(KycVerification::class);
    }

    public function borrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class);
    }

    public function activeBorrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class)->where('status', 'active');
    }

    public function scopeActive($query)
    {
        return $query->where('status', UserStatus::ACTIVE);
    }

    public function scopeEmailVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    public function scopeKycPending($query)
    {
        return $query->whereHas('kycVerification', fn ($q) => $q->where('status', KycVerificationStatus::PENDING->value));
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

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
            'status' => UserStatus::class,
        ];
    }
}

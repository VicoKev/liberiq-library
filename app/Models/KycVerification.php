<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\KycVerificationStatus;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable(['user_id', 'phone', 'status', 'rejection_reason', 'reviewed_by', 'reviewed_at'])]
final class KycVerification extends Model implements HasMedia
{
    use HasUuid, InteractsWithMedia, SoftDeletes;

    public function getIsPendingAttribute(): bool
    {
        return $this->status === KycVerificationStatus::PENDING->value;
    }

    public function getIsApprovedAttribute(): bool
    {
        return $this->status === KycVerificationStatus::APPROVED->value;
    }

    public function getIsRejectedAttribute(): bool
    {
        return $this->status === KycVerificationStatus::REJECTED->value;
    }

    public function getIdFrontUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('id_front') ?: null;
    }

    public function getIdBackUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('id_back') ?: null;
    }

    public function getSelfieUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('selfie') ?: null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', KycVerificationStatus::PENDING->value);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', KycVerificationStatus::APPROVED->value);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', KycVerificationStatus::REJECTED->value);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('id_front')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('id_back')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('selfie')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'status' => KycVerificationStatus::class,
        ];
    }
}

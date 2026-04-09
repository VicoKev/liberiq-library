<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BookStatus;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

#[Fillable(['author_id', 'category_id', 'title', 'slug', 'isbn', 'description', 'total_copies', 'available_copies', 'publication_year', 'language', 'publisher', 'status'])]
final class Book extends Model implements HasMedia
{
    use HasFactory, HasUuid, InteractsWithMedia, SoftDeletes;

    public function getIsAvailableAttribute(): bool
    {
        return $this->available_copies > 0 && $this->status === 'available';
    }

    public function getCoverUrlAttribute(): string
    {
        return $this->getFirstMediaUrl('cover') ?: asset('images/default-cover.png');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function borrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class);
    }

    public function activeBorrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class)->where('status', 'active');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')->where('available_copies', '>', 0);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%")
                ->orWhereHas('author', fn ($a) => $a->where('name', 'like', "%{$term}%"));
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(200)
            ->height(280)
            ->sharpen(10)
            ->nonQueued();

        $this->addMediaConversion('card')
            ->width(400)
            ->height(560)
            ->nonQueued();
    }

    protected function casts(): array
    {
        return [
            'total_copies' => 'integer',
            'available_copies' => 'integer',
            'publication_year' => 'integer',
            'status' => BookStatus::class,
        ];
    }
}

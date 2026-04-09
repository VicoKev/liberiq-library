<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['first_name', 'last_name', 'slug', 'bio', 'birth_date', 'nationality'])]
final class Author extends Model
{
    use HasFactory, HasUuid, SoftDeletes;

    public function books(): HasMany
    {
        return $this->hasMany(Book::class);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where('first_name', 'like', "%{$term}%")
            ->orWhere('last_name', 'like', "%{$term}%");
    }

    protected function casts(): array
    {
        return ['birth_date' => 'date'];
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BorrowingStatus;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'book_id', 'status', 'borrowed_at', 'due_at', 'returned_at', 'is_overdue', 'extensions_count', 'max_extensions', 'notes'])]
final class Borrowing extends Model
{
    use HasFactory, HasUuid;

    public function getCanExtendAttribute(): bool
    {
        return $this->status === 'active'
            && $this->extensions_count < $this->max_extensions;
    }

    public function getDaysOverdueAttribute(): int
    {
        if (! $this->is_overdue) {
            return 0;
        }

        $end = $this->returned_at ?? now();

        return (int) $this->due_at->diffInDays($end);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOverdue($query)
    {
        return $query->where('is_overdue', true)->whereNull('returned_at');
    }

    public function scopeForUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    protected function casts(): array
    {
        return [
            'borrowed_at' => 'datetime',
            'due_at' => 'datetime',
            'returned_at' => 'datetime',
            'is_overdue' => 'boolean',
            'extensions_count' => 'integer',
            'max_extensions' => 'integer',
            'status' => BorrowingStatus::class,
        ];
    }
}

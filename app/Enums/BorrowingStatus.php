<?php

declare(strict_types=1);

namespace App\Enums;

enum BorrowingStatus: string
{
    case ACTIVE = 'active';
    case RETURNED = 'returned';
    case OVERDUE = 'overdue';
    case EXTENDED = 'extended';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Actif',
            self::RETURNED => 'Retourné',
            self::OVERDUE => 'En retard',
            self::EXTENDED => 'Étendu',
        };
    }
}

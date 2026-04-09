<?php

declare(strict_types=1);

namespace App\Enums;

enum BookStatus: string
{
    case AVAILABLE = 'available';
    case CHECKED_OUT = 'checked_out';
    case RESERVED = 'reserved';
    case LOST = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Disponible',
            self::CHECKED_OUT => 'Emprunté',
            self::RESERVED => 'Réservé',
            self::LOST => 'Perdu',
        };
    }
}

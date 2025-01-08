<?php

namespace App\Entity\Enum;

enum AlertStatus: string
{
    case OPEN = 'open';
    case RESOLVED = 'resolved';

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }
}

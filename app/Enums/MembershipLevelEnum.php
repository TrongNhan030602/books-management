<?php

namespace App\Enums;

enum MembershipLevelEnum: string
{
    case Bronze = 'Bronze';
    case Silver = 'Silver';
    case Gold = 'Gold';

    public static function values(): array
    {
        return array_map(fn($level) => $level->value, self::cases());
    }
}
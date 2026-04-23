<?php

declare(strict_types=1);

namespace App\Enums;

enum EventType: string
{
    case Practice = 'practice';
    case RaceWeekend = 'race_weekend';

    public function label(): string
    {
        return match ($this) {
            self::Practice => 'Practice',
            self::RaceWeekend => 'Race Weekend',
        };
    }

    public function apiValue(): string
    {
        return match ($this) {
            self::Practice => 'GameModeType_PRACTICE',
            self::RaceWeekend => 'GameModeType_RACE_WEEKEND',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        $result = [];
        foreach (self::cases() as $case) {
            $result[$case->value] = $case->label();
        }

        return $result;
    }
}

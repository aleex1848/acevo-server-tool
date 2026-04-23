<?php

declare(strict_types=1);

namespace App\Enums;

enum RaceDurationType: string
{
    case Time = 'Time';
    case Laps = 'Laps';

    public function label(): string
    {
        return $this->value;
    }

    public function apiValue(): string
    {
        return match ($this) {
            self::Time => 'GameModeSelectionDuration_TIME',
            self::Laps => 'GameModeSelectionDuration_LAPS',
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

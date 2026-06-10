<?php

declare(strict_types=1);

namespace App\Enums;

enum WeatherBehaviour: string
{
    case Static = 'Static';
    case Dynamic = 'Dynamic';

    public function label(): string
    {
        return $this->value;
    }

    public function apiValue(): string
    {
        return match ($this) {
            self::Static => 'GameModeSelectionWeatherBehaviour_STATIC',
            self::Dynamic => 'GameModeSelectionWeatherBehaviour_DYNAMIC',
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

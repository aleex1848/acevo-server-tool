<?php

declare(strict_types=1);

namespace App\Enums;

enum WeatherType: string
{
    case Clear = 'Clear';
    case ScatteredClouds = 'Scattered Clouds';
    case BrokenClouds = 'Broken Clouds';
    case Overcast = 'Overcast';
    case Drizzle = 'Drizzle';
    case Rain = 'Rain';
    case HeavyRain = 'Heavy Rain';
    case Damp = 'Damp';

    public function label(): string
    {
        return $this->value;
    }

    public function apiValue(): string
    {
        return match ($this) {
            self::Clear => 'GameModeSelectionWeatherType_CLEAR',
            self::ScatteredClouds => 'GameModeSelectionWeatherType_SCATTERED_CLOUDS',
            self::BrokenClouds => 'GameModeSelectionWeatherType_BROKEN_CLOUDS',
            self::Overcast => 'GameModeSelectionWeatherType_OVERCAST',
            self::Drizzle => 'GameModeSelectionWeatherType_DRIZZLE',
            self::Rain => 'GameModeSelectionWeatherType_RAIN',
            self::HeavyRain => 'GameModeSelectionWeatherType_HEAVY_RAIN',
            self::Damp => 'GameModeSelectionWeatherType_DAMP',
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

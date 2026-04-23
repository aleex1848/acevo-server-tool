<?php

declare(strict_types=1);

namespace App\Enums;

enum InitialGrip: string
{
    case Green = 'Green';
    case Fast = 'Fast';
    case Optimum = 'Optimum';

    public function label(): string
    {
        return $this->value;
    }

    public function apiValue(): string
    {
        return match ($this) {
            self::Green => 'InitialGrip_GREEN',
            self::Fast => 'InitialGrip_FAST',
            self::Optimum => 'InitialGrip_OPTIMUM',
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

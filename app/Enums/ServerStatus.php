<?php

declare(strict_types=1);

namespace App\Enums;

enum ServerStatus: string
{
    case Starting = 'starting';
    case Running = 'running';
    case Stopped = 'stopped';
    case Failed = 'failed';
    case Exited = 'exited';

    public function label(): string
    {
        return match ($this) {
            self::Starting => 'Starting',
            self::Running => 'Running',
            self::Stopped => 'Stopped',
            self::Failed => 'Failed',
            self::Exited => 'Exited',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Starting => 'warning',
            self::Running => 'success',
            self::Stopped => 'gray',
            self::Failed => 'danger',
            self::Exited => 'gray',
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

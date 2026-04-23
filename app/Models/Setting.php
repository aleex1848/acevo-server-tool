<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property string $type
 */
final class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;

    private const CACHE_PREFIX = 'settings:';

    protected $guarded = [];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever(self::CACHE_PREFIX.$key, function () use ($key, $default): mixed {
            $setting = self::query()->where('key', $key)->first();

            if ($setting === null) {
                return $default;
            }

            return self::castFromStorage($setting->value, $setting->type);
        });
    }

    public static function setValue(string $key, mixed $value): self
    {
        $type = self::detectType($value);

        $setting = self::query()->updateOrCreate(
            ['key' => $key],
            ['value' => self::castToStorage($value), 'type' => $type],
        );

        Cache::forget(self::CACHE_PREFIX.$key);

        return $setting;
    }

    public static function flushCache(): void
    {
        foreach (self::query()->pluck('key') as $key) {
            Cache::forget(self::CACHE_PREFIX.$key);
        }
    }

    private static function castFromStorage(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => (bool) $value,
            'array' => json_decode($value, true),
            default => $value,
        };
    }

    private static function castToStorage(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return (string) $value;
    }

    private static function detectType(mixed $value): string
    {
        return match (true) {
            is_int($value) => 'int',
            is_float($value) => 'float',
            is_bool($value) => 'bool',
            is_array($value) => 'array',
            default => 'string',
        };
    }
}

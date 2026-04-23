<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class CarRepository
{
    private const PATH = 'cars.json';

    /**
     * @return Collection<int, array{name: string, display_name: string, performance_indicator: float, property_1: int, property_2: int, property_3: int}>
     */
    public function all(): Collection
    {
        $disk = Storage::disk('local');
        $mtime = $disk->lastModified(self::PATH);

        /** @var Collection<int, array{name: string, display_name: string, performance_indicator: float, property_1: int, property_2: int, property_3: int}> $cars */
        $cars = Cache::remember('cars:'.$mtime, now()->addDay(), function () use ($disk): Collection {
            $contents = $disk->get(self::PATH);

            if ($contents === null) {
                throw new RuntimeException('Unable to read '.self::PATH);
            }

            /** @var array{cars: array<int, array{name: string, display_name: string, performance_indicator: float, property_1: int, property_2: int, property_3: int}>} $decoded */
            $decoded = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);

            return collect($decoded['cars'] ?? [])->values();
        });

        return $cars;
    }

    /**
     * @return array<string, string> name => display_name (with PI)
     */
    public function options(): array
    {
        return $this->all()
            ->mapWithKeys(fn (array $car): array => [
                $car['name'] => sprintf(
                    '%s (PI %s)',
                    $car['display_name'],
                    number_format($car['performance_indicator'], 1, '.', ''),
                ),
            ])
            ->all();
    }

    /**
     * @return array{name: string, display_name: string, performance_indicator: float, property_1: int, property_2: int, property_3: int}|null
     */
    public function find(string $name): ?array
    {
        return $this->all()->firstWhere('name', $name);
    }
}

<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\EventType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use RuntimeException;

final class TrackRepository
{
    /**
     * @return Collection<int, array{track: string, layout: string, event_name: string, track_length: int, max_pit_slot: int, key: string, label: string}>
     */
    public function forType(EventType $type): Collection
    {
        $path = match ($type) {
            EventType::Practice => 'events_practice.json',
            EventType::RaceWeekend => 'events_race_weekend.json',
        };

        $file = resource_path('acevo/'.$path);

        if (! File::isFile($file)) {
            throw new RuntimeException("Acevo data file missing: {$path}");
        }

        $mtime = File::lastModified($file);

        /** @var Collection<int, array{track: string, layout: string, event_name: string, track_length: int, max_pit_slot: int, key: string, label: string}> $events */
        $events = Cache::remember(
            'tracks:'.$type->value.':'.$mtime,
            now()->addDay(),
            function () use ($file): Collection {
                $contents = File::get($file);

                /** @var array{events: array<int, array{track: string, layout: string, event_name: string, track_length: int, max_pit_slot: int}>} $decoded */
                $decoded = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);

                return collect($decoded['events'])->map(function (array $event): array {
                    $key = $event['track'].'|'.$event['layout'];
                    $km = number_format($event['track_length'] / 1000, 2, ',', '');

                    return [
                        'track' => $event['track'],
                        'layout' => $event['layout'],
                        'event_name' => $event['event_name'],
                        'track_length' => $event['track_length'],
                        'max_pit_slot' => $event['max_pit_slot'],
                        'key' => $key,
                        'label' => sprintf(
                            '%s %s [%skm] (pit:%d)',
                            $event['track'],
                            $event['layout'],
                            $km,
                            $event['max_pit_slot'],
                        ),
                    ];
                })->values();
            }
        );

        return $events;
    }

    /**
     * @return array<string, string> key => label, for Filament Select options
     */
    public function optionsForType(EventType $type): array
    {
        return $this->forType($type)
            ->mapWithKeys(fn (array $event): array => [$event['key'] => $event['label']])
            ->all();
    }

    /**
     * @return array{track: string, layout: string, event_name: string, track_length: int, max_pit_slot: int}|null
     */
    public function find(EventType $type, string $key): ?array
    {
        $event = $this->forType($type)->firstWhere('key', $key);

        if ($event === null) {
            return null;
        }

        return [
            'track' => $event['track'],
            'layout' => $event['layout'],
            'event_name' => $event['event_name'],
            'track_length' => $event['track_length'],
            'max_pit_slot' => $event['max_pit_slot'],
        ];
    }
}

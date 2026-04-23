<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\EventType;
use App\Enums\RaceDurationType;
use App\Models\ServerConfiguration;

final class ServerConfigGeneratorService
{
    /** Constant date matching the launcher examples. */
    public const DEFAULT_YEAR = 2024;

    public const DEFAULT_MONTH = 8;

    public const DEFAULT_DAY = 15;

    /**
     * @return array<string, mixed>
     */
    public function buildServerConfig(ServerConfiguration $configuration): array
    {
        $cars = [];

        foreach ($configuration->cars ?? [] as $car) {
            $cars[] = [
                'car_name' => (string) ($car['car_name'] ?? ''),
                'ballast' => (int) ($car['ballast'] ?? 0),
                'restrictor' => (float) ($car['restrictor'] ?? 0.0),
            ];
        }

        return [
            'server_tcp_listener_port' => $configuration->tcp_port,
            'server_udp_listener_port' => $configuration->udp_port,
            'server_tcp_internal_port' => $configuration->tcp_port,
            'server_udp_internal_port' => $configuration->udp_port,
            'server_http_port' => $configuration->http_port,
            'server_name' => $configuration->server_name,
            'max_players' => $configuration->max_players,
            'cycle' => $configuration->cycle,
            'allowed_cars_list_full' => $cars,
            'driver_password' => $configuration->driver_password ?? '',
            'spectator_password' => $configuration->spectator_password ?? '',
            'admin_password' => $configuration->admin_password ?? '',
            'type' => 'MultiplayerServerListSessionType_RANKED',
            'entry_list_path' => $configuration->entry_list_path ?? '',
            'results_path' => $configuration->results_path ?? '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildSeasonDefinition(ServerConfiguration $configuration): array
    {
        $sessions = $configuration->sessions ?? [];

        $gameConfig = match ($configuration->type) {
            EventType::Practice => $this->practiceGameConfig($sessions),
            EventType::RaceWeekend => $this->raceWeekendGameConfig($sessions),
        };

        return [
            'game_type' => $configuration->type->apiValue(),
            'event' => [
                'track' => $configuration->track,
                'layout' => $configuration->layout,
                'event_name' => $configuration->event_name,
                'track_length' => (string) $configuration->track_length,
            ],
            'export_json' => false,
            'game_config' => $gameConfig,
            'weather_type' => $configuration->weather_type->apiValue(),
            'weather_behaviour' => $configuration->weather_behaviour->apiValue(),
            'initial_grip' => $configuration->initial_grip->apiValue(),
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $sessions
     * @return array<string, mixed>
     */
    private function practiceGameConfig(array $sessions): array
    {
        $practice = $sessions['practice'] ?? [];

        return [
            'practice_duration' => (int) ($practice['duration'] ?? 0),
            'practice_time_of_day' => $this->timeOfDay($practice),
            'practice_overtime_waiting_next_session' => (int) ($practice['overtime_wait_next_session'] ?? 0),
            'practice_max_wait_to_box' => (int) ($practice['max_wait_to_box'] ?? 0),
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $sessions
     * @return array<string, mixed>
     */
    private function raceWeekendGameConfig(array $sessions): array
    {
        $practice = $sessions['practice'] ?? [];
        $qualify = $sessions['qualify'] ?? [];
        $warmup = $sessions['warmup'] ?? [];
        $race = $sessions['race'] ?? [];

        $raceDurationType = isset($race['duration_type']) && $race['duration_type'] !== null
            ? RaceDurationType::from((string) $race['duration_type'])
            : RaceDurationType::Time;

        return [
            'practice_duration' => (int) ($practice['duration'] ?? 0),
            'practice_time_of_day' => $this->timeOfDay($practice),
            'practice_overtime_waiting_next_session' => (int) ($practice['overtime_wait_next_session'] ?? 0),
            'practice_max_wait_to_box' => (int) ($practice['max_wait_to_box'] ?? 0),

            'qualify_duration' => (int) ($qualify['duration'] ?? 0),
            'qualify_time_of_day' => $this->timeOfDay($qualify),
            'qualify_overtime_waiting_next_session' => (int) ($qualify['overtime_wait_next_session'] ?? 0),
            'qualify_max_wait_to_box' => (int) ($qualify['max_wait_to_box'] ?? 0),

            'warmup_duration' => (int) ($warmup['duration'] ?? 0),
            'warmup_time_of_day' => $this->timeOfDay($warmup),
            'warmup_overtime_waiting_next_session' => (int) ($warmup['overtime_wait_next_session'] ?? 0),
            'warmup_max_wait_to_box' => (int) ($warmup['max_wait_to_box'] ?? 0),

            'race_duration' => (int) ($race['duration'] ?? 0),
            'race_duration_type' => $raceDurationType->apiValue(),
            'race_time_of_day' => $this->timeOfDay($race),
            'race_max_wait_to_box' => (int) ($race['max_wait_to_box'] ?? 0),
            'race_min_waiting_players' => (int) ($race['min_waiting_players'] ?? 0),
            'race_max_waiting_players' => (int) ($race['max_waiting_players'] ?? 0),
        ];
    }

    /**
     * @param  array<string, mixed>  $session
     * @return array<string, int>
     */
    private function timeOfDay(array $session): array
    {
        return [
            'year' => self::DEFAULT_YEAR,
            'month' => self::DEFAULT_MONTH,
            'day' => self::DEFAULT_DAY,
            'hour' => (int) ($session['hour'] ?? 16),
            'minute' => (int) ($session['minute'] ?? 0),
            'second' => 0,
            'time_multiplier' => (int) ($session['time_multiplier'] ?? 1),
        ];
    }
}

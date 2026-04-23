<?php

declare(strict_types=1);

use App\Enums\EventType;
use App\Models\ServerConfiguration;
use App\Services\ServerConfigGeneratorService;

it('builds the server configuration JSON shape', function (): void {
    $config = ServerConfiguration::factory()->make([
        'server_name' => 'aleex GT',
        'tcp_port' => 9700,
        'udp_port' => 9700,
        'http_port' => 8080,
        'max_players' => 16,
        'cycle' => true,
        'driver_password' => '',
        'admin_password' => 'acr',
        'spectator_password' => null,
        'entry_list_path' => null,
        'results_path' => null,
        'cars' => [
            ['car_name' => 'preset_r8gt4_mech_1', 'ballast' => 0, 'restrictor' => 0.0],
            ['car_name' => 'preset_m4gt3_mech_1', 'ballast' => 0, 'restrictor' => 0.0],
        ],
    ]);

    $result = app(ServerConfigGeneratorService::class)->buildServerConfig($config);

    expect($result)->toMatchArray([
        'server_tcp_listener_port' => 9700,
        'server_udp_listener_port' => 9700,
        'server_tcp_internal_port' => 9700,
        'server_udp_internal_port' => 9700,
        'server_http_port' => 8080,
        'server_name' => 'aleex GT',
        'max_players' => 16,
        'cycle' => true,
        'driver_password' => '',
        'spectator_password' => '',
        'admin_password' => 'acr',
        'type' => 'MultiplayerServerListSessionType_RANKED',
        'entry_list_path' => '',
        'results_path' => '',
    ]);

    expect($result['allowed_cars_list_full'])->toBe([
        ['car_name' => 'preset_r8gt4_mech_1', 'ballast' => 0, 'restrictor' => 0.0],
        ['car_name' => 'preset_m4gt3_mech_1', 'ballast' => 0, 'restrictor' => 0.0],
    ]);
});

it('builds a practice season definition', function (): void {
    $config = ServerConfiguration::factory()->make([
        'type' => EventType::Practice,
        'track' => 'Nurburgring',
        'layout' => 'Touristenfahrten',
        'event_name' => 'Touristenfahrten Time Attack',
        'track_length' => 19300,
        'sessions' => [
            'practice' => [
                'duration' => 14400,
                'hour' => 16,
                'minute' => 0,
                'time_multiplier' => 2,
                'max_wait_to_box' => 10,
                'overtime_wait_next_session' => 10,
            ],
        ],
    ]);

    $result = app(ServerConfigGeneratorService::class)->buildSeasonDefinition($config);

    expect($result['game_type'])->toBe('GameModeType_PRACTICE');
    expect($result['event'])->toBe([
        'track' => 'Nurburgring',
        'layout' => 'Touristenfahrten',
        'event_name' => 'Touristenfahrten Time Attack',
        'track_length' => '19300',
    ]);
    expect($result['game_config']['practice_duration'])->toBe(14400);
    expect($result['game_config']['practice_time_of_day'])->toBe([
        'year' => 2024,
        'month' => 8,
        'day' => 15,
        'hour' => 16,
        'minute' => 0,
        'second' => 0,
        'time_multiplier' => 2,
    ]);
    expect($result['game_config']['practice_overtime_waiting_next_session'])->toBe(10);
    expect($result['game_config']['practice_max_wait_to_box'])->toBe(10);

    expect($result['game_config'])->not->toHaveKey('qualify_duration');
    expect($result['game_config'])->not->toHaveKey('race_duration');
});

it('builds a race weekend season definition', function (): void {
    $config = ServerConfiguration::factory()->raceWeekend()->make();

    $result = app(ServerConfigGeneratorService::class)->buildSeasonDefinition($config);

    expect($result['game_type'])->toBe('GameModeType_RACE_WEEKEND');
    expect($result['weather_behaviour'])->toBe('GameModeSelectionWeatherBehaviour_STATIC');
    expect($result['weather_type'])->toBe('GameModeSelectionWeatherType_SCATTERED_CLOUDS');
    expect($result['initial_grip'])->toBe('InitialGrip_FAST');

    expect($result['game_config'])->toHaveKeys([
        'practice_duration',
        'qualify_duration',
        'qualify_time_of_day',
        'warmup_duration',
        'race_duration',
        'race_duration_type',
        'race_time_of_day',
        'race_min_waiting_players',
        'race_max_waiting_players',
    ]);

    expect($result['game_config']['qualify_duration'])->toBe(420);
    expect($result['game_config']['race_duration'])->toBe(600);
    expect($result['game_config']['race_duration_type'])->toBe('GameModeSelectionDuration_TIME');
    expect($result['game_config']['race_time_of_day']['hour'])->toBe(14);
});

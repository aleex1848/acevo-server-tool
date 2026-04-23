<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EventType;
use App\Enums\InitialGrip;
use App\Enums\WeatherBehaviour;
use App\Enums\WeatherType;
use App\Models\ServerConfiguration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServerConfiguration>
 */
final class ServerConfigurationFactory extends Factory
{
    protected $model = ServerConfiguration::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'server_name' => fake()->words(2, true).' Server',
            'tcp_port' => 9700,
            'udp_port' => 9700,
            'http_port' => 8080,
            'external_http_port' => 8080,
            'max_players' => 16,
            'cycle' => true,
            'driver_password' => '',
            'admin_password' => 'acr',
            'spectator_password' => null,
            'entry_list_path' => null,
            'results_path' => null,
            'type' => EventType::Practice,
            'track' => 'Nurburgring',
            'layout' => 'Touristenfahrten',
            'event_name' => 'Touristenfahrten Time Attack',
            'track_length' => 19300,
            'initial_grip' => InitialGrip::Green,
            'weather_behaviour' => WeatherBehaviour::Static,
            'weather_type' => WeatherType::Clear,
            'sessions' => [
                'practice' => [
                    'duration' => 300,
                    'hour' => 16,
                    'minute' => 0,
                    'time_multiplier' => 1,
                    'max_wait_to_box' => 10,
                    'overtime_wait_next_session' => 10,
                ],
            ],
            'cars' => [
                ['car_name' => 'preset_r8gt4_mech_1', 'ballast' => 0, 'restrictor' => 0.0],
            ],
        ];
    }

    public function raceWeekend(): self
    {
        return $this->state(fn (): array => [
            'type' => EventType::RaceWeekend,
            'track' => 'Road Atlanta',
            'layout' => 'GP',
            'event_name' => 'GP Race',
            'track_length' => 4088,
            'initial_grip' => InitialGrip::Fast,
            'weather_type' => WeatherType::ScatteredClouds,
            'sessions' => [
                'practice' => [
                    'duration' => 0,
                    'hour' => 14,
                    'minute' => 0,
                    'time_multiplier' => 1,
                    'max_wait_to_box' => 10,
                    'overtime_wait_next_session' => 10,
                ],
                'qualify' => [
                    'duration' => 420,
                    'hour' => 14,
                    'minute' => 0,
                    'time_multiplier' => 1,
                    'max_wait_to_box' => 10,
                    'overtime_wait_next_session' => 10,
                ],
                'warmup' => [
                    'duration' => 0,
                    'hour' => 14,
                    'minute' => 0,
                    'time_multiplier' => 1,
                    'max_wait_to_box' => 10,
                    'overtime_wait_next_session' => 10,
                ],
                'race' => [
                    'duration' => 600,
                    'duration_type' => 'Time',
                    'hour' => 14,
                    'minute' => 0,
                    'time_multiplier' => 2,
                    'max_wait_to_box' => 10,
                    'overtime_wait_next_session' => 10,
                    'min_waiting_players' => 1,
                    'max_waiting_players' => 30,
                ],
            ],
        ]);
    }
}

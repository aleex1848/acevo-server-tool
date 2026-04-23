<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ServerStatus;
use App\Models\Server;
use App\Models\ServerConfiguration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Server>
 */
final class ServerFactory extends Factory
{
    protected $model = Server::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'server_configuration_id' => ServerConfiguration::factory(),
            'container_id' => null,
            'container_name' => 'acevo-server-'.fake()->unique()->randomNumber(6),
            'status' => ServerStatus::Running,
            'tcp_port' => fake()->numberBetween(9700, 9720),
            'udp_port' => fake()->numberBetween(9700, 9720),
            'external_http_port' => fake()->numberBetween(8080, 9000),
            'started_at' => now(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EventType;
use App\Enums\InitialGrip;
use App\Enums\ServerStatus;
use App\Enums\WeatherBehaviour;
use App\Enums\WeatherType;
use Database\Factories\ServerConfigurationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property string $server_name
 * @property int $tcp_port
 * @property int $udp_port
 * @property int $http_port
 * @property int $external_http_port
 * @property int $max_players
 * @property bool $cycle
 * @property string $driver_password
 * @property string $admin_password
 * @property string|null $spectator_password
 * @property string|null $entry_list_path
 * @property string|null $results_path
 * @property EventType $type
 * @property string $track
 * @property string $layout
 * @property string $event_name
 * @property int $track_length
 * @property InitialGrip $initial_grip
 * @property WeatherBehaviour $weather_behaviour
 * @property WeatherType $weather_type
 * @property array<string, array<string, mixed>>|null $sessions
 * @property array<int, array{car_name: string, ballast: int, restrictor: float}>|null $cars
 * @property int|null $user_id
 */
final class ServerConfiguration extends Model
{
    /** @use HasFactory<ServerConfigurationFactory> */
    use HasFactory;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Server, $this>
     */
    public function servers(): HasMany
    {
        return $this->hasMany(Server::class);
    }

    /**
     * @return HasMany<Server, $this>
     */
    public function activeServers(): HasMany
    {
        return $this->servers()->whereIn('status', [
            ServerStatus::Starting->value,
            ServerStatus::Running->value,
        ]);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cycle' => 'boolean',
            'sessions' => 'array',
            'cars' => 'array',
            'type' => EventType::class,
            'initial_grip' => InitialGrip::class,
            'weather_behaviour' => WeatherBehaviour::class,
            'weather_type' => WeatherType::class,
        ];
    }
}

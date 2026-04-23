<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ServerStatus;
use Database\Factories\ServerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $server_configuration_id
 * @property string|null $container_id
 * @property string $container_name
 * @property ServerStatus $status
 * @property int $tcp_port
 * @property int $udp_port
 * @property int $external_http_port
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $stopped_at
 * @property string|null $last_error
 * @property-read ServerConfiguration $serverConfiguration
 */
final class Server extends Model
{
    /** @use HasFactory<ServerFactory> */
    use HasFactory;

    protected $guarded = [];

    public function serverConfiguration(): BelongsTo
    {
        return $this->belongsTo(ServerConfiguration::class);
    }

    public function isRunning(): bool
    {
        return in_array($this->status, [ServerStatus::Starting, ServerStatus::Running], true);
    }

    /**
     * @return array<int, array{host: int, container: int, protocol: string}>
     */
    public function portMappings(): array
    {
        return [
            ['host' => $this->tcp_port, 'container' => $this->tcp_port, 'protocol' => 'tcp'],
            ['host' => $this->udp_port, 'container' => $this->udp_port, 'protocol' => 'udp'],
            ['host' => $this->external_http_port, 'container' => 8080, 'protocol' => 'tcp'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ServerStatus::class,
            'started_at' => 'datetime',
            'stopped_at' => 'datetime',
        ];
    }
}

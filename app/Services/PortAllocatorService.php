<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ServerConfiguration;

final class PortAllocatorService
{
    public function __construct(
        private readonly AcevoSettingsService $settings,
    ) {}

    /**
     * Returns the next free port in the game port range (tcp/udp share the range)
     * that is not already stored on any ServerConfiguration.
     */
    public function nextFreeGamePort(?int $exclude = null): ?int
    {
        $range = $this->settings->gamePortRange();

        $used = $this->usedGamePorts($exclude);

        for ($port = $range['start']; $port <= $range['end']; $port++) {
            if (! in_array($port, $used, true)) {
                return $port;
            }
        }

        return null;
    }

    public function nextFreeHttpPort(?int $exclude = null): ?int
    {
        $range = $this->settings->httpPortRange();

        $used = $this->usedHttpPorts($exclude);

        for ($port = $range['start']; $port <= $range['end']; $port++) {
            if (! in_array($port, $used, true)) {
                return $port;
            }
        }

        return null;
    }

    /**
     * Number of configurations that could still be created with the current ranges.
     */
    public function availableSlots(): int
    {
        $gameRange = $this->settings->gamePortRange();
        $httpRange = $this->settings->httpPortRange();

        $gameFree = $this->countFree($gameRange, $this->usedGamePorts());
        $httpFree = $this->countFree($httpRange, $this->usedHttpPorts());

        return min($gameFree, $httpFree);
    }

    /**
     * @return list<int>
     */
    private function usedGamePorts(?int $excludeConfigurationId = null): array
    {
        $query = ServerConfiguration::query();

        if ($excludeConfigurationId !== null) {
            $query->whereKeyNot($excludeConfigurationId);
        }

        $tcp = $query->pluck('tcp_port')->all();
        $udp = ServerConfiguration::query()
            ->when($excludeConfigurationId !== null, fn ($q) => $q->whereKeyNot($excludeConfigurationId))
            ->pluck('udp_port')
            ->all();

        return array_values(array_unique([...array_map('intval', $tcp), ...array_map('intval', $udp)]));
    }

    /**
     * @return list<int>
     */
    private function usedHttpPorts(?int $excludeConfigurationId = null): array
    {
        $query = ServerConfiguration::query();

        if ($excludeConfigurationId !== null) {
            $query->whereKeyNot($excludeConfigurationId);
        }

        return array_values(array_unique(array_map('intval', $query->pluck('external_http_port')->all())));
    }

    /**
     * @param  array{start: int, end: int}  $range
     * @param  list<int>  $used
     */
    private function countFree(array $range, array $used): int
    {
        $total = max(0, $range['end'] - $range['start'] + 1);

        $usedInRange = 0;
        foreach ($used as $port) {
            if ($port >= $range['start'] && $port <= $range['end']) {
                $usedInRange++;
            }
        }

        return max(0, $total - $usedInRange);
    }
}

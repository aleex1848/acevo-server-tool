<?php

declare(strict_types=1);

use App\Models\ServerConfiguration;
use App\Services\AcevoSettingsService;
use App\Services\PortAllocatorService;

beforeEach(function (): void {
    $settings = app(AcevoSettingsService::class);
    $settings->saveGamePortRange(9700, 9703);
    $settings->saveHttpPortRange(8080, 8083);
});

it('returns the first port in the range when nothing is used', function (): void {
    expect(app(PortAllocatorService::class)->nextFreeGamePort())->toBe(9700);
    expect(app(PortAllocatorService::class)->nextFreeHttpPort())->toBe(8080);
});

it('skips ports already used by configurations (tcp and udp share the range)', function (): void {
    ServerConfiguration::factory()->create([
        'tcp_port' => 9700,
        'udp_port' => 9701,
        'external_http_port' => 8080,
    ]);

    expect(app(PortAllocatorService::class)->nextFreeGamePort())->toBe(9702);
    expect(app(PortAllocatorService::class)->nextFreeHttpPort())->toBe(8081);
});

it('returns null when the range is exhausted', function (): void {
    foreach ([9700, 9701, 9702, 9703] as $index => $port) {
        ServerConfiguration::factory()->create([
            'tcp_port' => $port,
            'udp_port' => $port,
            'external_http_port' => 8080 + $index,
        ]);
    }

    expect(app(PortAllocatorService::class)->nextFreeGamePort())->toBeNull();
    expect(app(PortAllocatorService::class)->nextFreeHttpPort())->toBeNull();
});

it('returns the minimum of free game and http ports as available slots', function (): void {
    ServerConfiguration::factory()->create([
        'tcp_port' => 9700,
        'udp_port' => 9700,
        'external_http_port' => 8080,
    ]);
    ServerConfiguration::factory()->create([
        'tcp_port' => 9701,
        'udp_port' => 9701,
        'external_http_port' => 8081,
    ]);

    expect(app(PortAllocatorService::class)->availableSlots())->toBe(2);
});

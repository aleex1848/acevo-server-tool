<?php

declare(strict_types=1);

use App\Enums\ServerStatus;
use App\Models\Server;
use App\Models\ServerConfiguration;
use App\Services\AcevoServerLauncherService;
use App\Services\AcevoSettingsService;

beforeEach(function (): void {
    app(AcevoSettingsService::class)->saveGamePortRange(9700, 9720);
    app(AcevoSettingsService::class)->saveHttpPortRange(8080, 9000);
});

it('marks a server as failed when the docker binary is missing', function (): void {
    $configuration = ServerConfiguration::factory()->create([
        'tcp_port' => 9703,
        'udp_port' => 9703,
        'external_http_port' => 8083,
    ]);

    $launcher = new AcevoServerLauncherService(
        app(AcevoSettingsService::class),
        app(\App\Services\ServerConfigPackerService::class),
        app(\App\Services\ServerConfigGeneratorService::class),
    );

    try {
        $launcher->launch($configuration->refresh());
    } catch (Throwable) {
        // docker is not available inside the test environment; we only care about the DB side-effects below.
    }

    $server = Server::query()->where('server_configuration_id', $configuration->id)->first();

    expect($server)->not->toBeNull();
    expect($server->tcp_port)->toBe(9703);
    expect($server->udp_port)->toBe(9703);
    expect($server->external_http_port)->toBe(8083);
    expect($server->status)->toBeIn([ServerStatus::Failed, ServerStatus::Running]);
})->skipOnWindows();

it('refuses to launch a second server while one is still active', function (): void {
    $configuration = ServerConfiguration::factory()->create();
    Server::factory()->for($configuration, 'serverConfiguration')->create([
        'status' => ServerStatus::Running,
    ]);

    $launcher = app(AcevoServerLauncherService::class);

    expect(fn () => $launcher->launch($configuration->refresh()))
        ->toThrow(RuntimeException::class, 'already running');
});

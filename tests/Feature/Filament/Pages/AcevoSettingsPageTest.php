<?php

declare(strict_types=1);

use App\Filament\Pages\AcevoSettings;
use App\Models\User;
use App\Services\AcevoSettingsService;

use function Pest\Livewire\livewire;

it('renders for admin users', function (): void {
    livewire(AcevoSettings::class)->assertOk();
});

it('loads current settings into the form', function (): void {
    app(AcevoSettingsService::class)->saveDockerImage('custom/image:tag');
    app(AcevoSettingsService::class)->saveGamePortRange(9800, 9820);
    app(AcevoSettingsService::class)->saveHttpPortRange(8100, 8200);

    livewire(AcevoSettings::class)
        ->assertSchemaStateSet([
            'docker_image' => 'custom/image:tag',
            'port_game_start' => 9800,
            'port_game_end' => 9820,
            'port_http_start' => 8100,
            'port_http_end' => 8200,
        ]);
});

it('saves the form state back to the settings store', function (): void {
    livewire(AcevoSettings::class)
        ->fillForm([
            'docker_image' => 'other/image:2.0',
            'port_game_start' => 9600,
            'port_game_end' => 9650,
            'port_http_start' => 7000,
            'port_http_end' => 7100,
        ])
        ->call('save')
        ->assertNotified();

    $service = app(AcevoSettingsService::class);
    expect($service->dockerImage())->toBe('other/image:2.0');
    expect($service->gamePortRange())->toMatchArray(['start' => 9600, 'end' => 9650]);
    expect($service->httpPortRange())->toMatchArray(['start' => 7000, 'end' => 7100]);
});

it('blocks non-admin users', function (): void {
    $user = User::factory()->create(['is_admin' => false]);
    $this->actingAs($user);

    expect(AcevoSettings::canAccess())->toBeFalse();
});

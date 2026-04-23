<?php

declare(strict_types=1);

use App\Enums\EventType;
use App\Filament\Resources\ServerConfigurations\Pages\CreateServerConfiguration;
use App\Filament\Resources\ServerConfigurations\Pages\EditServerConfiguration;
use App\Filament\Resources\ServerConfigurations\Pages\ListServerConfigurations;
use App\Filament\Resources\ServerConfigurations\Pages\ViewServerConfiguration;
use App\Models\ServerConfiguration;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

it('can render the index page', function (): void {
    livewire(ListServerConfigurations::class)->assertOk();
});

it('can render the create page', function (): void {
    livewire(CreateServerConfiguration::class)->assertOk();
});

it('can render the edit page and load state', function (): void {
    $config = ServerConfiguration::factory()->create();

    livewire(EditServerConfiguration::class, ['record' => $config->id])
        ->assertOk()
        ->assertSchemaStateSet([
            'name' => $config->name,
            'server_name' => $config->server_name,
            'track' => $config->track,
            'layout' => $config->layout,
            'max_players' => $config->max_players,
        ]);
});

it('can render the view page with preview action', function (): void {
    $config = ServerConfiguration::factory()->create();

    livewire(ViewServerConfiguration::class, ['record' => $config->id])
        ->assertOk()
        ->assertActionExists('preview');
});

it('can create a practice configuration', function (): void {
    livewire(CreateServerConfiguration::class)
        ->fillForm([
            'name' => 'My Test Practice',
            'server_name' => 'Test GT',
            'tcp_port' => 9700,
            'udp_port' => 9700,
            'http_port' => 8080,
            'max_players' => 16,
            'cycle' => true,
            'admin_password' => 'acr',
            'type' => EventType::Practice->value,
            'track_key' => 'Nurburgring|Touristenfahrten',
            'initial_grip' => 'Green',
            'weather_behaviour' => 'Static',
            'weather_type' => 'Clear',
            'sessions.practice.duration' => 300,
            'sessions.practice.hour' => 16,
            'sessions.practice.minute' => 0,
            'sessions.practice.time_multiplier' => 1,
            'sessions.practice.max_wait_to_box' => 10,
            'sessions.practice.overtime_wait_next_session' => 10,
        ])
        ->call('create')
        ->assertNotified()
        ->assertHasNoFormErrors();

    assertDatabaseHas(ServerConfiguration::class, [
        'name' => 'My Test Practice',
        'server_name' => 'Test GT',
        'type' => EventType::Practice->value,
        'track' => 'Nurburgring',
        'layout' => 'Touristenfahrten',
    ]);
});

it('requires a name', function (): void {
    livewire(CreateServerConfiguration::class)
        ->fillForm(['name' => null])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

it('can update an existing configuration', function (): void {
    $config = ServerConfiguration::factory()->create();

    livewire(EditServerConfiguration::class, ['record' => $config->id])
        ->fillForm(['server_name' => 'Updated Server Name'])
        ->call('save')
        ->assertNotified()
        ->assertHasNoFormErrors();

    assertDatabaseHas(ServerConfiguration::class, [
        'id' => $config->id,
        'server_name' => 'Updated Server Name',
    ]);
});

it('has expected table columns', function (string $column): void {
    livewire(ListServerConfigurations::class)->assertTableColumnExists($column);
})->with(['name', 'server_name', 'type', 'track', 'max_players', 'updated_at']);

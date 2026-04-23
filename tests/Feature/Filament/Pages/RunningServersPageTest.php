<?php

declare(strict_types=1);

use App\Enums\ServerStatus;
use App\Filament\Pages\RunningServers;
use App\Models\Server;
use App\Models\ServerConfiguration;

use function Pest\Livewire\livewire;

it('renders the running servers page', function (): void {
    livewire(RunningServers::class)->assertOk();
});

it('lists servers in the table', function (): void {
    $configuration = ServerConfiguration::factory()->create();
    $server = Server::factory()
        ->for($configuration, 'serverConfiguration')
        ->create([
            'status' => ServerStatus::Running,
        ]);

    livewire(RunningServers::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$server]);
});

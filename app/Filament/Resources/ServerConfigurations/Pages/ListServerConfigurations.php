<?php

declare(strict_types=1);

namespace App\Filament\Resources\ServerConfigurations\Pages;

use App\Filament\Resources\ServerConfigurations\ServerConfigurationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListServerConfigurations extends ListRecords
{
    protected static string $resource = ServerConfigurationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

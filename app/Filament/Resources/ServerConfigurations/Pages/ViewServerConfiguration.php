<?php

declare(strict_types=1);

namespace App\Filament\Resources\ServerConfigurations\Pages;

use App\Filament\Resources\ServerConfigurations\Actions\PreviewConfigAction;
use App\Filament\Resources\ServerConfigurations\ServerConfigurationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;

final class ViewServerConfiguration extends ViewRecord
{
    protected static string $resource = ServerConfigurationResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function getHeaderActions(): array
    {
        return [
            PreviewConfigAction::make(),
            EditAction::make(),
        ];
    }
}

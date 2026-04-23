<?php

declare(strict_types=1);

namespace App\Filament\Resources\ServerConfigurations\Pages;

use App\Filament\Resources\ServerConfigurations\Actions\PreviewConfigAction;
use App\Filament\Resources\ServerConfigurations\ServerConfigurationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

final class EditServerConfiguration extends EditRecord
{
    protected static string $resource = ServerConfigurationResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    protected function getHeaderActions(): array
    {
        return [
            PreviewConfigAction::make(),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

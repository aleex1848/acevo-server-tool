<?php

declare(strict_types=1);

namespace App\Filament\Resources\ServerConfigurations\Pages;

use App\Filament\Resources\ServerConfigurations\ServerConfigurationResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

final class CreateServerConfiguration extends CreateRecord
{
    protected static string $resource = ServerConfigurationResource::class;

    protected Width|string|null $maxContentWidth = Width::Full;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }
}

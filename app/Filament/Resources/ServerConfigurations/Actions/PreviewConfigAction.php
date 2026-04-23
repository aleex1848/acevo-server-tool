<?php

declare(strict_types=1);

namespace App\Filament\Resources\ServerConfigurations\Actions;

use App\Models\ServerConfiguration;
use App\Services\ServerConfigGeneratorService;
use App\Services\ServerConfigPackerService;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\View\View;

final class PreviewConfigAction
{
    public static function make(): Action
    {
        return Action::make('preview')
            ->label('Preview configs')
            ->icon(Heroicon::CodeBracket)
            ->color('primary')
            ->modalHeading('Generated Server Configurations')
            ->modalWidth('7xl')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close')
            ->modalContent(function (ServerConfiguration $record): View {
                $generator = app(ServerConfigGeneratorService::class);
                $packer = app(ServerConfigPackerService::class);

                $serverConfig = $generator->buildServerConfig($record);
                $seasonDefinition = $generator->buildSeasonDefinition($record);

                $serverConfigJson = json_encode(
                    $serverConfig,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                );

                $seasonDefinitionJson = json_encode(
                    $seasonDefinition,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                );

                return view('filament.resources.server-configurations.preview', [
                    'serverConfigJson' => $serverConfigJson,
                    'seasonDefinitionJson' => $seasonDefinitionJson,
                    'serverConfigB64' => $packer->pack($serverConfig),
                    'seasonDefinitionB64' => $packer->pack($seasonDefinition),
                ]);
            });
    }
}

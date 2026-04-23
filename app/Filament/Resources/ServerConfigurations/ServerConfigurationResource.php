<?php

declare(strict_types=1);

namespace App\Filament\Resources\ServerConfigurations;

use App\Filament\Resources\ServerConfigurations\Pages\CreateServerConfiguration;
use App\Filament\Resources\ServerConfigurations\Pages\EditServerConfiguration;
use App\Filament\Resources\ServerConfigurations\Pages\ListServerConfigurations;
use App\Filament\Resources\ServerConfigurations\Pages\ViewServerConfiguration;
use App\Filament\Resources\ServerConfigurations\Schemas\ServerConfigurationForm;
use App\Filament\Resources\ServerConfigurations\Schemas\ServerConfigurationInfolist;
use App\Filament\Resources\ServerConfigurations\Tables\ServerConfigurationsTable;
use App\Models\ServerConfiguration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

final class ServerConfigurationResource extends Resource
{
    protected static ?string $model = ServerConfiguration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedServerStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Server Launcher';

    protected static ?string $modelLabel = 'Server Configuration';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name',
            'server_name',
            'track',
            'layout',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return ServerConfigurationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ServerConfigurationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServerConfigurationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServerConfigurations::route('/'),
            'create' => CreateServerConfiguration::route('/create'),
            'view' => ViewServerConfiguration::route('/{record}'),
            'edit' => EditServerConfiguration::route('/{record}/edit'),
        ];
    }
}

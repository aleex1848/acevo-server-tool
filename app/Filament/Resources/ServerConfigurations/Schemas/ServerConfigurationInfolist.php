<?php

declare(strict_types=1);

namespace App\Filament\Resources\ServerConfigurations\Schemas;

use App\Enums\EventType;
use App\Models\ServerConfiguration;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ServerConfigurationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
            Grid::make(['default' => 1, 'xl' => 3])->schema([
                Section::make('Server Info')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('name')->label('Configuration'),
                        TextEntry::make('server_name')->label('Server name'),
                        TextEntry::make('tcp_port')->label('TCP Port'),
                        TextEntry::make('udp_port')->label('UDP Port'),
                        TextEntry::make('http_port')->label('HTTP Port'),
                        TextEntry::make('max_players')->label('Max players'),
                        TextEntry::make('cycle')
                            ->label('Cycle')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Yes' : 'No'),
                    ]),
                Section::make('Event')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('type')
                            ->badge()
                            ->formatStateUsing(fn (EventType $state): string => $state->label()),
                        TextEntry::make('track')->label('Track'),
                        TextEntry::make('layout')->label('Layout'),
                        TextEntry::make('event_name')->label('Event name'),
                        TextEntry::make('track_length')
                            ->label('Length')
                            ->formatStateUsing(fn (int $state): string => number_format($state / 1000, 2).' km'),
                        TextEntry::make('initial_grip'),
                        TextEntry::make('weather_type')->label('Weather'),
                        TextEntry::make('weather_behaviour')->label('Weather behavior'),
                    ]),
                Section::make('Cars & Sessions')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('cars')
                            ->label('Cars')
                            ->state(fn (ServerConfiguration $record): string => count($record->cars ?? []).' selected'),
                        TextEntry::make('sessions')
                            ->label('Sessions')
                            ->state(function (ServerConfiguration $record): string {
                                $keys = array_keys($record->sessions ?? []);

                                return $keys === [] ? '-' : implode(', ', array_map('ucfirst', $keys));
                            }),
                    ]),
            ]),
            ]);
    }
}

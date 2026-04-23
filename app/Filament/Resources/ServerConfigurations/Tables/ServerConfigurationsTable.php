<?php

declare(strict_types=1);

namespace App\Filament\Resources\ServerConfigurations\Tables;

use App\Enums\EventType;
use App\Models\ServerConfiguration;
use App\Services\AcevoServerLauncherService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Throwable;

final class ServerConfigurationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Configuration')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('server_name')
                    ->label('Server name')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (EventType $state): string => $state->label())
                    ->color(fn (EventType $state): string => match ($state) {
                        EventType::Practice => 'info',
                        EventType::RaceWeekend => 'success',
                    }),
                TextColumn::make('track')
                    ->label('Track')
                    ->formatStateUsing(fn ($record): string => trim($record->track.' '.$record->layout))
                    ->searchable(),
                TextColumn::make('tcp_port')
                    ->label('Ports')
                    ->toggleable()
                    ->state(fn (ServerConfiguration $record): string => "{$record->tcp_port}/{$record->udp_port} · {$record->external_http_port}"),
                TextColumn::make('max_players')
                    ->label('Max players')
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_running')
                    ->label('Running')
                    ->boolean()
                    ->state(fn (ServerConfiguration $record): bool => $record->activeServers()->exists()),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([])
            ->recordActions([
                Action::make('launch')
                    ->label('Launch')
                    ->icon(Heroicon::OutlinedRocketLaunch)
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Launch Acevo server?')
                    ->modalDescription(fn (ServerConfiguration $record): string => "A Docker container for \"{$record->name}\" will be started.")
                    ->visible(fn (ServerConfiguration $record): bool => ! $record->activeServers()->exists())
                    ->action(function (ServerConfiguration $record): void {
                        try {
                            $server = app(AcevoServerLauncherService::class)->launch($record);

                            Notification::make()
                                ->title('Server started')
                                ->body("Container: {$server->container_name}")
                                ->success()
                                ->send();
                        } catch (Throwable $e) {
                            Notification::make()
                                ->title('Launch failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

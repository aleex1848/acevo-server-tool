<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\ServerStatus;
use App\Models\Server;
use App\Services\AcevoServerLauncherService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Throwable;

final class RunningServers extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.pages.running-servers';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPlayCircle;

    protected static ?string $navigationLabel = 'Running Servers';

    protected static ?string $title = 'Running Servers';

    protected static ?int $navigationSort = 10;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Server::query()->with('serverConfiguration'))
            ->poll('5s')
            ->columns([
                TextColumn::make('serverConfiguration.name')
                    ->label('Configuration')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('container_name')
                    ->label('Container')
                    ->toggleable()
                    ->copyable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (ServerStatus $state): string => $state->label())
                    ->color(fn (ServerStatus $state): string => $state->color()),
                TextColumn::make('tcp_port')->label('TCP')->toggleable(),
                TextColumn::make('udp_port')->label('UDP')->toggleable(),
                TextColumn::make('external_http_port')->label('HTTP')->toggleable(),
                TextColumn::make('started_at')->dateTime()->since()->sortable(),
            ])
            ->defaultSort('started_at', 'desc')
            ->recordActions([
                Action::make('stop')
                    ->label('Stop')
                    ->icon(Heroicon::OutlinedStop)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Server $record): bool => $record->isRunning())
                    ->action(function (Server $record): void {
                        try {
                            app(AcevoServerLauncherService::class)->stop($record);

                            Notification::make()
                                ->title('Server stopped')
                                ->success()
                                ->send();
                        } catch (Throwable $e) {
                            Notification::make()
                                ->title('Stop failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('restart')
                    ->label('Restart')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->requiresConfirmation()
                    ->visible(fn (Server $record): bool => $record->isRunning())
                    ->action(function (Server $record): void {
                        try {
                            app(AcevoServerLauncherService::class)->restart($record);

                            Notification::make()
                                ->title('Server restarted')
                                ->success()
                                ->send();
                        } catch (Throwable $e) {
                            Notification::make()
                                ->title('Restart failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('logs')
                    ->label('Logs')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->modalHeading(fn (Server $record): string => 'Logs: '.$record->container_name)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->visible(fn (Server $record): bool => $record->container_id !== null)
                    ->modalContent(fn (Server $record) => view(
                        'filament.pages.partials.server-logs',
                        ['logs' => app(AcevoServerLauncherService::class)->logs($record)],
                    )),
                Action::make('forget')
                    ->label('Remove record')
                    ->icon(Heroicon::OutlinedTrash)
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (Server $record): bool => ! $record->isRunning())
                    ->action(fn (Server $record) => $record->delete()),
            ]);
    }
}

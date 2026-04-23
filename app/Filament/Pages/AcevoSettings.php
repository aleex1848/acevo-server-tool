<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Services\AcevoSettingsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;

/**
 * @property-read \Filament\Schemas\Schema $form
 */
final class AcevoSettings extends Page
{
    protected string $view = 'filament.pages.acevo-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Acevo Settings';

    protected static ?string $title = 'Acevo Settings';

    protected static ?int $navigationSort = 90;

    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    public static function canAccess(): bool
    {
        return Gate::allows('manage-acevo-settings');
    }

    public function getHeading(): string
    {
        return 'Acevo Server Settings';
    }

    public function mount(): void
    {
        abort_unless(self::canAccess(), 403);

        $service = app(AcevoSettingsService::class);
        $gameRange = $service->gamePortRange();
        $httpRange = $service->httpPortRange();

        $this->form->fill([
            'docker_image' => $service->dockerImage(),
            'port_game_start' => $gameRange['start'],
            'port_game_end' => $gameRange['end'],
            'port_http_start' => $httpRange['start'],
            'port_http_end' => $httpRange['end'],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Section::make('Docker')
                    ->description('Container image used when launching a server.')
                    ->schema([
                        TextInput::make('docker_image')
                            ->label('Docker image')
                            ->required()
                            ->maxLength(255)
                            ->placeholder(AcevoSettingsService::DEFAULT_DOCKER_IMAGE),
                    ]),
                Section::make('Game port range (TCP/UDP)')
                    ->description('Range used for the game server TCP and UDP ports. Limits the number of simultaneous servers.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('port_game_start')
                                ->label('Start')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(65535)
                                ->required(),
                            TextInput::make('port_game_end')
                                ->label('End')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(65535)
                                ->required()
                                ->gte('port_game_start'),
                        ]),
                    ]),
                Section::make('HTTP port range')
                    ->description('External HTTP ports mapped to container port 8080.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('port_http_start')
                                ->label('Start')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(65535)
                                ->required(),
                            TextInput::make('port_http_end')
                                ->label('End')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(65535)
                                ->required()
                                ->gte('port_http_start'),
                        ]),
                    ]),
            ]);
    }

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save')
                ->icon(Heroicon::OutlinedCheck)
                ->action('save'),
        ];
    }

    public function save(): void
    {
        abort_unless(self::canAccess(), 403);

        /** @var array{docker_image: string, port_game_start: int|string, port_game_end: int|string, port_http_start: int|string, port_http_end: int|string} $data */
        $data = $this->form->getState();

        $service = app(AcevoSettingsService::class);
        $service->saveDockerImage((string) $data['docker_image']);
        $service->saveGamePortRange((int) $data['port_game_start'], (int) $data['port_game_end']);
        $service->saveHttpPortRange((int) $data['port_http_start'], (int) $data['port_http_end']);

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }
}

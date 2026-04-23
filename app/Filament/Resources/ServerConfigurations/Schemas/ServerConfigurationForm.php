<?php

declare(strict_types=1);

namespace App\Filament\Resources\ServerConfigurations\Schemas;

use App\Enums\EventType;
use App\Enums\InitialGrip;
use App\Enums\RaceDurationType;
use App\Enums\WeatherBehaviour;
use App\Enums\WeatherType;
use App\Services\AcevoSettingsService;
use App\Services\PortAllocatorService;
use App\Support\CarRepository;
use App\Support\TrackRepository;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

final class ServerConfigurationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
            Grid::make()
                ->columns(['default' => 1, 'lg' => 2, '2xl' => 3])
                ->schema([
                    self::serverInfoSection(),
                    self::eventSettingsSection(),
                    self::sessionsSection(),
                ]),
            ]);
    }

    /**
     * @param  array{start: int, end: int}  $range
     */
    private static function rangeHelperText(array $range): string
    {
        return "Allowed range: {$range['start']}-{$range['end']}.";
    }

    private static function serverInfoSection(): Section
    {
        return Section::make('Server Info')
            ->description('Basic server metadata, ports, and access.')
            ->columnSpan(1)
            ->schema([
                TextInput::make('name')
                    ->label('Configuration name')
                    ->helperText('Internal label to identify this saved configuration.')
                    ->required()
                    ->maxLength(255),
                TextInput::make('server_name')
                    ->required()
                    ->maxLength(255)
                    ->default('EVO Server'),
                Grid::make(2)->schema([
                    TextInput::make('tcp_port')
                        ->label('TCP Port')
                        ->numeric()
                        ->minValue(fn (): int => app(AcevoSettingsService::class)->gamePortRange()['start'])
                        ->maxValue(fn (): int => app(AcevoSettingsService::class)->gamePortRange()['end'])
                        ->required()
                        ->default(fn (): ?int => app(PortAllocatorService::class)->nextFreeGamePort())
                        ->helperText(fn (): string => self::rangeHelperText(app(AcevoSettingsService::class)->gamePortRange())),
                    TextInput::make('udp_port')
                        ->label('UDP Port')
                        ->numeric()
                        ->minValue(fn (): int => app(AcevoSettingsService::class)->gamePortRange()['start'])
                        ->maxValue(fn (): int => app(AcevoSettingsService::class)->gamePortRange()['end'])
                        ->required()
                        ->default(fn (): ?int => app(PortAllocatorService::class)->nextFreeGamePort()),
                ]),
                Grid::make(2)->schema([
                    TextInput::make('external_http_port')
                        ->label('HTTP Port (external)')
                        ->helperText(fn (): string => 'Mapped to internal 8080. '.self::rangeHelperText(app(AcevoSettingsService::class)->httpPortRange()))
                        ->numeric()
                        ->minValue(fn (): int => app(AcevoSettingsService::class)->httpPortRange()['start'])
                        ->maxValue(fn (): int => app(AcevoSettingsService::class)->httpPortRange()['end'])
                        ->required()
                        ->default(fn (): ?int => app(PortAllocatorService::class)->nextFreeHttpPort()),
                    TextInput::make('max_players')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(99)
                        ->required()
                        ->default(16),
                ]),
                Hidden::make('http_port')->default(8080),
                Toggle::make('cycle')
                    ->label('Cycle')
                    ->default(true),
                TextInput::make('driver_password')
                    ->password()
                    ->revealable()
                    ->default('')
                    ->maxLength(255),
                TextInput::make('admin_password')
                    ->password()
                    ->revealable()
                    ->default('')
                    ->maxLength(255),
                TextInput::make('spectator_password')
                    ->password()
                    ->revealable()
                    ->maxLength(255),
                TextInput::make('entry_list_path')
                    ->label('Entry List path')
                    ->maxLength(1024),
                TextInput::make('results_path')
                    ->label('Results save folder')
                    ->maxLength(1024),
            ]);
    }

    private static function eventSettingsSection(): Section
    {
        return Section::make('Event Settings')
            ->description('Type, weather, track and cars.')
            ->columnSpan(1)
            ->schema([
                Select::make('type')
                    ->options(EventType::options())
                    ->required()
                    ->live()
                    ->default(EventType::Practice->value)
                    ->afterStateUpdated(function (Set $set): void {
                        $set('track_key', null);
                        $set('track', '');
                        $set('layout', '');
                        $set('event_name', '');
                        $set('track_length', 0);
                    }),

                Grid::make(2)->schema([
                    Select::make('initial_grip')
                        ->options(InitialGrip::options())
                        ->default(InitialGrip::Green->value)
                        ->required(),
                    Select::make('weather_behaviour')
                        ->label('Weather Behavior')
                        ->options(WeatherBehaviour::options())
                        ->disableOptionWhen(fn (string $value): bool => in_array(
                            $value,
                            WeatherBehaviour::disabledValues(),
                            true,
                        ))
                        ->default(WeatherBehaviour::Static->value)
                        ->required(),
                ]),

                Select::make('weather_type')
                    ->label('Weather')
                    ->options(WeatherType::options())
                    ->default(WeatherType::Clear->value)
                    ->required(),

                Select::make('track_key')
                    ->label('Track')
                    ->options(function (Get $get): array {
                        $type = $get('type');

                        if (! is_string($type) || $type === '') {
                            return [];
                        }

                        return app(TrackRepository::class)->optionsForType(EventType::from($type));
                    })
                    ->searchable()
                    ->required()
                    ->live()
                    ->dehydrated(false)
                    ->afterStateHydrated(function (Select $component, Get $get): void {
                        $track = $get('track');
                        $layout = $get('layout');

                        if (is_string($track) && is_string($layout) && $track !== '' && $layout !== '') {
                            $component->state($track.'|'.$layout);
                        }
                    })
                    ->afterStateUpdated(function (?string $state, Set $set, Get $get): void {
                        if ($state === null) {
                            return;
                        }

                        $type = $get('type');

                        if (! is_string($type) || $type === '') {
                            return;
                        }

                        $event = app(TrackRepository::class)->find(EventType::from($type), $state);

                        if ($event === null) {
                            return;
                        }

                        $set('track', $event['track']);
                        $set('layout', $event['layout']);
                        $set('event_name', $event['event_name']);
                        $set('track_length', $event['track_length']);
                    }),

                Hidden::make('track'),
                Hidden::make('layout'),
                Hidden::make('event_name'),
                Hidden::make('track_length'),

                Repeater::make('cars')
                    ->label('Cars')
                    ->helperText('Select the cars allowed on the server. Add ballast/restrictor per car.')
                    ->schema([
                        Select::make('car_name')
                            ->label('Car')
                            ->options(fn (): array => app(CarRepository::class)->options())
                            ->searchable()
                            ->required()
                            ->distinct()
                            ->columnSpanFull(),
                        Grid::make(2)->schema([
                            TextInput::make('ballast')
                                ->numeric()
                                ->default(0)
                                ->minValue(0)
                                ->maxValue(200),
                            TextInput::make('restrictor')
                                ->numeric()
                                ->default(0.0)
                                ->step(0.1)
                                ->minValue(0)
                                ->maxValue(1),
                        ]),
                    ])
                    ->itemLabel(function (array $state): ?string {
                        $name = $state['car_name'] ?? null;

                        if (! is_string($name) || $name === '') {
                            return null;
                        }

                        $car = app(CarRepository::class)->find($name);

                        return $car['display_name'] ?? $name;
                    })
                    ->collapsed()
                    ->collapsible()
                    ->cloneable()
                    ->defaultItems(0)
                    ->reorderable(false)
                    ->addActionLabel('Add car'),
            ]);
    }

    private static function sessionsSection(): Section
    {
        return Section::make('Sessions')
            ->description('Session durations and time of day settings.')
            ->columnSpan(1)
            ->schema([
                self::sessionBlock('practice', 'Practice')
                    ->visible(fn (Get $get): bool => $get('type') !== null && $get('type') !== ''),
                self::sessionBlock('qualify', 'Qualify')
                    ->visible(fn (Get $get): bool => $get('type') === EventType::RaceWeekend->value),
                self::sessionBlock('warmup', 'Warmup')
                    ->visible(fn (Get $get): bool => $get('type') === EventType::RaceWeekend->value),
                self::raceBlock()
                    ->visible(fn (Get $get): bool => $get('type') === EventType::RaceWeekend->value),
            ]);
    }

    private static function sessionBlock(string $key, string $label): Section
    {
        return Section::make($label)
            ->compact()
            ->schema([
                TextInput::make('sessions.'.$key.'.duration')
                    ->label('Duration (sec)')
                    ->numeric()
                    ->minValue(0)
                    ->default(300)
                    ->required(),
                Grid::make(3)->schema([
                    TextInput::make('sessions.'.$key.'.hour')
                        ->label('Hour')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(23)
                        ->default(16)
                        ->required(),
                    TextInput::make('sessions.'.$key.'.minute')
                        ->label('Minute')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(59)
                        ->default(0)
                        ->required(),
                    TextInput::make('sessions.'.$key.'.time_multiplier')
                        ->label('Time multiplier')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(60)
                        ->default(1)
                        ->required(),
                ]),
                Grid::make(2)->schema([
                    TextInput::make('sessions.'.$key.'.max_wait_to_box')
                        ->label('Max wait to box (sec)')
                        ->numeric()
                        ->minValue(0)
                        ->default(10)
                        ->required(),
                    TextInput::make('sessions.'.$key.'.overtime_wait_next_session')
                        ->label('Overtime wait next session (sec)')
                        ->numeric()
                        ->minValue(0)
                        ->default(10)
                        ->required(),
                ]),
            ]);
    }

    private static function raceBlock(): Section
    {
        return Section::make('Race')
            ->compact()
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('sessions.race.duration')
                        ->label('Duration')
                        ->numeric()
                        ->minValue(0)
                        ->default(600)
                        ->required(),
                    Select::make('sessions.race.duration_type')
                        ->label('Duration type')
                        ->options(RaceDurationType::options())
                        ->default(RaceDurationType::Time->value)
                        ->required(),
                ]),
                Grid::make(3)->schema([
                    TextInput::make('sessions.race.hour')
                        ->label('Hour')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(23)
                        ->default(14)
                        ->required(),
                    TextInput::make('sessions.race.minute')
                        ->label('Minute')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(59)
                        ->default(0)
                        ->required(),
                    TextInput::make('sessions.race.time_multiplier')
                        ->label('Time multiplier')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(60)
                        ->default(1)
                        ->required(),
                ]),
                Grid::make(2)->schema([
                    TextInput::make('sessions.race.max_wait_to_box')
                        ->label('Max wait to box (sec)')
                        ->numeric()
                        ->minValue(0)
                        ->default(10)
                        ->required(),
                    TextInput::make('sessions.race.overtime_wait_next_session')
                        ->label('Overtime wait next session (sec)')
                        ->numeric()
                        ->minValue(0)
                        ->default(10)
                        ->required(),
                ]),
                Grid::make(2)->schema([
                    TextInput::make('sessions.race.min_waiting_players')
                        ->label('Min waiting players')
                        ->numeric()
                        ->minValue(0)
                        ->default(1)
                        ->required(),
                    TextInput::make('sessions.race.max_waiting_players')
                        ->label('Max waiting players')
                        ->numeric()
                        ->minValue(0)
                        ->default(30)
                        ->required(),
                ]),
            ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;

final class AcevoSettingsService
{
    public const KEY_DOCKER_IMAGE = 'acevo.docker_image';

    public const KEY_PORT_GAME_START = 'acevo.port_game_start';

    public const KEY_PORT_GAME_END = 'acevo.port_game_end';

    public const KEY_PORT_HTTP_START = 'acevo.port_http_start';

    public const KEY_PORT_HTTP_END = 'acevo.port_http_end';

    public const DEFAULT_DOCKER_IMAGE = 'aleex1848/acevo-server:latest';

    public const DEFAULT_GAME_PORT_START = 9700;

    public const DEFAULT_GAME_PORT_END = 9720;

    public const DEFAULT_HTTP_PORT_START = 8080;

    public const DEFAULT_HTTP_PORT_END = 9000;

    public function dockerImage(): string
    {
        /** @var string $image */
        $image = Setting::getValue(self::KEY_DOCKER_IMAGE, self::DEFAULT_DOCKER_IMAGE);

        return $image;
    }

    /**
     * @return array{start: int, end: int}
     */
    public function gamePortRange(): array
    {
        return [
            'start' => (int) Setting::getValue(self::KEY_PORT_GAME_START, self::DEFAULT_GAME_PORT_START),
            'end' => (int) Setting::getValue(self::KEY_PORT_GAME_END, self::DEFAULT_GAME_PORT_END),
        ];
    }

    /**
     * @return array{start: int, end: int}
     */
    public function httpPortRange(): array
    {
        return [
            'start' => (int) Setting::getValue(self::KEY_PORT_HTTP_START, self::DEFAULT_HTTP_PORT_START),
            'end' => (int) Setting::getValue(self::KEY_PORT_HTTP_END, self::DEFAULT_HTTP_PORT_END),
        ];
    }

    public function saveDockerImage(string $image): void
    {
        Setting::setValue(self::KEY_DOCKER_IMAGE, $image);
    }

    public function saveGamePortRange(int $start, int $end): void
    {
        Setting::setValue(self::KEY_PORT_GAME_START, $start);
        Setting::setValue(self::KEY_PORT_GAME_END, $end);
    }

    public function saveHttpPortRange(int $start, int $end): void
    {
        Setting::setValue(self::KEY_PORT_HTTP_START, $start);
        Setting::setValue(self::KEY_PORT_HTTP_END, $end);
    }
}

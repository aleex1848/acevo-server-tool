<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ServerStatus;
use App\Models\Server;
use App\Models\ServerConfiguration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Docker\DockerContainer;
use Symfony\Component\Process\Process;
use Throwable;

final class AcevoServerLauncherService
{
    public function __construct(
        private readonly AcevoSettingsService $settings,
        private readonly ServerConfigPackerService $packer,
        private readonly ServerConfigGeneratorService $generator,
    ) {}

    public function launch(ServerConfiguration $configuration): Server
    {
        if ($configuration->activeServers()->exists()) {
            throw new RuntimeException('A server is already running for this configuration.');
        }

        $serverConfigPayload = $this->packer->pack(
            $this->generator->buildServerConfig($configuration),
        );
        $seasonPayload = $this->packer->pack(
            $this->generator->buildSeasonDefinition($configuration),
        );

        $server = Server::query()->create([
            'server_configuration_id' => $configuration->id,
            'container_name' => $this->buildContainerName($configuration),
            'status' => ServerStatus::Starting,
            'tcp_port' => $configuration->tcp_port,
            'udp_port' => $configuration->udp_port,
            'external_http_port' => $configuration->external_http_port,
        ]);

        try {
            $environment = [
                'STEAM_APP_ID' => '4564210',
                'STARTUP_COMMAND' => 'wine AssettoCorsaEVOServer.exe -serverconfig ${SERVERCONFIG} -seasondefinition ${SEASONDEFINITION}',
                'FAST_BOOT' => 'true',
                'SERVERCONFIG' => $serverConfigPayload,
                'SEASONDEFINITION' => $seasonPayload,
            ];

            $envFile = $this->writeEnvFile($environment);

            $container = $this->buildContainer($configuration, $server->container_name, $envFile);
            $instance = $container->start();

            $server->update([
                'container_id' => $instance->getDockerIdentifier(),
                'status' => ServerStatus::Running,
                'started_at' => Carbon::now(),
            ]);

            @unlink($envFile);

            return $server->refresh();
        } catch (Throwable $e) {
            $toThrow = $this->wrapDockerSocketPermissionHint($e);
            $server->update([
                'status' => ServerStatus::Failed,
                'last_error' => $toThrow->getMessage(),
                'stopped_at' => Carbon::now(),
            ]);

            throw $toThrow;
        }
    }

    public function stop(Server $server): Server
    {
        if ($server->container_id === null) {
            $server->update([
                'status' => ServerStatus::Stopped,
                'stopped_at' => Carbon::now(),
            ]);

            return $server;
        }

        $process = new Process(['docker', 'stop', $server->container_id]);
        $process->setTimeout(60);
        $process->run();

        $remove = new Process(['docker', 'rm', '-f', $server->container_id]);
        $remove->setTimeout(30);
        $remove->run();

        $server->update([
            'status' => ServerStatus::Stopped,
            'stopped_at' => Carbon::now(),
            'last_error' => $process->isSuccessful() ? null : mb_trim($process->getErrorOutput()),
        ]);

        return $server->refresh();
    }

    public function restart(Server $server): Server
    {
        $configuration = $server->serverConfiguration;
        $this->stop($server);

        return $this->launch($configuration);
    }

    public function syncStatus(Server $server): Server
    {
        if ($server->container_id === null) {
            return $server;
        }

        $process = new Process(['docker', 'inspect', '--format', '{{.State.Status}}', $server->container_id]);
        $process->setTimeout(10);
        $process->run();

        if (! $process->isSuccessful()) {
            $server->update([
                'status' => ServerStatus::Exited,
                'stopped_at' => $server->stopped_at ?? Carbon::now(),
            ]);

            return $server->refresh();
        }

        $dockerStatus = mb_trim($process->getOutput());

        $status = match ($dockerStatus) {
            'running', 'restarting' => ServerStatus::Running,
            'created' => ServerStatus::Starting,
            'exited', 'dead' => ServerStatus::Exited,
            'paused' => ServerStatus::Stopped,
            default => $server->status,
        };

        if ($status !== $server->status) {
            $server->update([
                'status' => $status,
                'stopped_at' => $status === ServerStatus::Exited ? ($server->stopped_at ?? Carbon::now()) : $server->stopped_at,
            ]);
        }

        return $server->refresh();
    }

    public function logs(Server $server, int $tail = 200): string
    {
        if ($server->container_id === null) {
            return '';
        }

        $process = new Process(['docker', 'logs', '--tail', (string) $tail, $server->container_id]);
        $process->setTimeout(10);
        $process->run();

        return $process->getOutput().$process->getErrorOutput();
    }

    private function buildContainer(ServerConfiguration $configuration, string $name, string $envFile): DockerContainer
    {
        return DockerContainer::create($this->settings->dockerImage())
            ->name($name)
            ->doNotCleanUpAfterExit()
            ->mapPort($configuration->tcp_port, $configuration->tcp_port, 'tcp')
            ->mapPort($configuration->udp_port, $configuration->udp_port, 'udp')
            ->mapPort($configuration->external_http_port, 8080, 'tcp')
            ->setOptionalArgs('--restart', 'unless-stopped', '--env-file', escapeshellarg($envFile));
    }

    private function buildContainerName(ServerConfiguration $configuration): string
    {
        return 'acevo-server-'.$configuration->id.'-'.Str::lower(Str::random(6));
    }

    /**
     * Writes environment variables to a temporary file in docker's --env-file format
     * (KEY=VALUE, one per line) to avoid shell escaping issues.
     *
     * @param  array<string, string>  $environment
     */
    private function writeEnvFile(array $environment): string
    {
        $path = tempnam(sys_get_temp_dir(), 'acevo-env-');

        if ($path === false) {
            throw new RuntimeException('Unable to create env file.');
        }

        $lines = [];
        foreach ($environment as $key => $value) {
            if (str_contains($value, "\n")) {
                throw new RuntimeException("Environment value for {$key} must not contain newlines.");
            }

            $lines[] = $key.'='.$value;
        }

        file_put_contents($path, implode("\n", $lines)."\n");

        return $path;
    }

    private function wrapDockerSocketPermissionHint(Throwable $e): Throwable
    {
        $message = $e->getMessage();
        if (
            str_contains($message, 'permission denied')
            && str_contains($message, 'docker.sock')
        ) {
            return new RuntimeException(
                $message.' — Docker-Socket nicht beschreibbar für den PHP-User. '
                .'Im Stack: Volume `/var/run/docker.sock` mounten und `group_add` mit der numerischen GID '
                .'der Host-Gruppe `docker` setzen (auf dem Host: `stat -c \'%g\' /var/run/docker.sock`).',
                0,
                $e
            );
        }

        return $e;
    }
}

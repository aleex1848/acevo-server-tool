<?php

declare(strict_types=1);

use App\Services\AcevoSettingsService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->timestamps();
        });

        $now = now();

        DB::table('settings')->insert([
            [
                'key' => AcevoSettingsService::KEY_DOCKER_IMAGE,
                'value' => AcevoSettingsService::DEFAULT_DOCKER_IMAGE,
                'type' => 'string',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => AcevoSettingsService::KEY_PORT_GAME_START,
                'value' => (string) AcevoSettingsService::DEFAULT_GAME_PORT_START,
                'type' => 'int',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => AcevoSettingsService::KEY_PORT_GAME_END,
                'value' => (string) AcevoSettingsService::DEFAULT_GAME_PORT_END,
                'type' => 'int',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => AcevoSettingsService::KEY_PORT_HTTP_START,
                'value' => (string) AcevoSettingsService::DEFAULT_HTTP_PORT_START,
                'type' => 'int',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => AcevoSettingsService::KEY_PORT_HTTP_END,
                'value' => (string) AcevoSettingsService::DEFAULT_HTTP_PORT_END,
                'type' => 'int',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

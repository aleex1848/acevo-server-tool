<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('server_configurations', function (Blueprint $table): void {
            $table->id();
            $table->string('name');

            $table->string('server_name')->default('');
            $table->unsignedInteger('tcp_port')->default(9700);
            $table->unsignedInteger('udp_port')->default(9700);
            $table->unsignedInteger('http_port')->default(8080);
            $table->unsignedInteger('max_players')->default(16);
            $table->boolean('cycle')->default(true);

            $table->string('driver_password')->nullable();
            $table->string('admin_password')->nullable();
            $table->string('spectator_password')->nullable();
            $table->string('entry_list_path')->nullable();
            $table->string('results_path')->nullable();

            $table->string('type')->default('practice');
            $table->string('track')->default('');
            $table->string('layout')->default('');
            $table->string('event_name')->default('');
            $table->unsignedInteger('track_length')->default(0);

            $table->string('initial_grip')->default('Green');
            $table->string('weather_behaviour')->default('Static');
            $table->string('weather_type')->default('Clear');

            $table->json('sessions')->nullable();
            $table->json('cars')->nullable();

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('server_configurations');
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('server_configuration_id')->constrained()->cascadeOnDelete();
            $table->string('container_id', 64)->nullable();
            $table->string('container_name')->unique();
            $table->string('status')->default('starting');
            $table->unsignedInteger('tcp_port');
            $table->unsignedInteger('udp_port');
            $table->unsignedInteger('external_http_port');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('stopped_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('container_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('server_configurations', function (Blueprint $table): void {
            $table->unsignedInteger('external_http_port')->default(8080)->after('http_port');
        });
    }

    public function down(): void
    {
        Schema::table('server_configurations', function (Blueprint $table): void {
            $table->dropColumn('external_http_port');
        });
    }
};

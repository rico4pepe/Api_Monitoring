<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('alert_definitions', function (Blueprint $table) {
             $table->id();

            $table->string('name');

            $table->string('scope_type'); // global, vendor, client, endpoint
            $table->unsignedBigInteger('scope_id')->nullable();

            $table->string('metric');     // failure_percent, latency_p95, total_volume
            $table->string('comparison'); // >, >=, <, <=

            $table->decimal('warn_threshold', 10, 2)->nullable();
            $table->decimal('critical_threshold', 10, 2)->nullable();

            $table->unsignedBigInteger('min_volume')->nullable();

            $table->string('evaluation_period'); // e.g. 5m, 15m, 1h

            $table->string('owner_type'); // team, vendor, user, client
            $table->unsignedBigInteger('owner_id');

            $table->boolean('is_active')->default(false);

            $table->timestamps();

            $table->index(['scope_type', 'scope_id']);
            $table->index(['metric']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alert_definitions');
    }
};

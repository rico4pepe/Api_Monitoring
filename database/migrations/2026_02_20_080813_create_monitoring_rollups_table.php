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
            Schema::create('monitoring_rollups', function (Blueprint $table) {
            $table->id();

            // Minute-aligned bucket (e.g., 2026-02-20 10:03:00)
            $table->dateTime('time_bucket');

            // Dimensions
            $table->string('service_type', 50);
            $table->string('vendor_code', 50)->nullable();
            $table->string('client_code', 50)->nullable();

            // Metrics
            $table->unsignedInteger('total')->default(0);
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->unsignedInteger('pending_count')->default(0);

            $table->decimal('avg_latency', 10, 2)->default(0); // ms
            $table->decimal('failure_rate', 5, 2)->default(0);  // percentage

            $table->timestamps();

            // Composite unique constraint for idempotency
            $table->unique(
                ['time_bucket', 'service_type', 'vendor_code', 'client_code'],
                'rollup_unique_bucket_dimension'
            );

            // Index for fast time-based queries
            $table->index('time_bucket');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoring_rollups');
    }
};

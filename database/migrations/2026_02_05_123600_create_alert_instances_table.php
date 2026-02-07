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
        Schema::create('alert_instances', function (Blueprint $table) {
          $table->id();

            $table->foreignId('alert_definition_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('current_state'); // OK, WARN, CRITICAL
            $table->string('last_state')->nullable();

            $table->timestamp('last_evaluated_at')->nullable();
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamp('last_recovered_at')->nullable();

            $table->timestamps();

            $table->unique(['alert_definition_id']);
            $table->index(['current_state']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alert_instances');
    }
};

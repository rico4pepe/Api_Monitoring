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
        Schema::create('alert_notifications', function (Blueprint $table) {
           $table->id();

            $table->foreignId('alert_instance_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('state'); // WARN, CRITICAL, RECOVERED
            $table->string('channel'); // email, slack, sms
            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            $table->index(['state']);
            $table->index(['channel']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alert_notifications');
    }
};

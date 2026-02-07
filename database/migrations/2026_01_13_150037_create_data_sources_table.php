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
        Schema::create('data_sources', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();  // zenith_sms, providus_sms, electricity_rechargenow
            $table->string('type');  // database, api, webhook
            $table->json('config');  // connection info, table name, endpoint, auth keys
            $table->string('cursor')->nullable();  // last ID or timestamp processed
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_polled_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_sources');
    }
};

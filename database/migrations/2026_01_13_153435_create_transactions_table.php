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
        Schema::create('transactions', function (Blueprint $table) {
          $table->bigIncrements('id');

        $table->string('source');  // bank, internal, scheduler, webhook

        $table->string('client_code')->nullable();  // ZENITH, GTBANK, PROVIDUS, etc

        $table->string('service_type');  // SMS_OTP, SMS_TRANS, ELECTRICITY, BETTING, PAYMENT

        $table->string('vendor_code')->nullable();  // MTN, AIRTEL, RECHARGENOW, etc

        $table->string('endpoint')->nullable();

        $table->string('reference')->nullable();  // bank ref or internal ref

        $table->string('phone')->nullable();

        $table->enum('status', ['SUCCESS','FAILED','PENDING']);

        $table->string('error_code')->nullable();

        $table->integer('latency_ms')->nullable();

        $table->json('raw_request')->nullable();
        $table->json('raw_response')->nullable();

        $table->timestamp('occurred_at');  // when it actually happened

        $table->timestamps();

        $table->index(['client_code','service_type']);
        $table->index(['vendor_code','service_type']);
        $table->index(['occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

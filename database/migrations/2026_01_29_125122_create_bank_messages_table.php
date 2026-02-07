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
        Schema::create('bank_messages', function (Blueprint $table) {
           //  auto-increment control here
            $table->bigIncrements('id');

            // Bank-local user reference (not enforced)
            $table->unsignedBigInteger('user_id')->nullable();

            // Destination phone number
            $table->text('msisdn')->nullable();

            // SMS segmentation
            $table->integer('pages')->default(1);

            // Message content
            $table->text('text')->nullable();

            // Gateway response
            $table->text('response')->nullable();

            // Delivery status (bank-specific)
            $table->string('dlr_status', 20)->default('0');

            // Delivery acknowledgement flag
            $table->integer('dlr_report')->default(0);

            // DLR enabled flag
            $table->enum('dlr', ['1','0'])->nullable();

            // Processing status (bank meaning)
            $table->enum('status', ['1','0'])->default('0');

            // Sender ID
            $table->string('senderid', 50)->nullable();

            // Retry / resend indicator
            $table->enum('counter', ['0','1'])->default('0');

            // Raw DLR request payload
            $table->text('dlr_request')->nullable();

            // Raw DLR result payload
            $table->text('dlr_results')->nullable();

            // Network / telco
            $table->string('network', 10)->nullable();

            // User callback URL
            $table->text('user_dlr_url')->nullable();

            // Timestamps
            $table->timestamps();

            // Indexes (mirroring bank structure where meaningful)
            $table->index('user_id');
            $table->index('pages');
            $table->index('dlr_status');
            $table->index('dlr_report');
            $table->index('created_at');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_messages');
    }
};

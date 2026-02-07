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
        Schema::create('incidents', function (Blueprint $table) {
           
                $table->id();

                $table->string('service_type');   // SMS
                $table->string('scope_type');     // bank | telco | global
                $table->string('scope_code')->nullable(); // ZENITH, MTN, etc

                $table->string('status'); // open | acknowledged | resolved
                $table->decimal('failure_rate', 5, 2);
                $table->timestamp('started_at');
                $table->timestamp('resolved_at')->nullable();

                $table->timestamps();

                $table->index(['service_type', 'status']);
                  $table->index(['scope_type', 'scope_code']);
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};

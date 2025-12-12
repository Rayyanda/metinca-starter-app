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
        Schema::create('operation_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operation_id')->constrained()->onDelete('cascade');
            $table->string('param_type');      // temperature, pressure, time, speed
            $table->string('param_name');      // 'Wax Temp', 'Inject Time', 'Dry Time'
            $table->string('unit');            // °C, PSI, minutes, hours
            $table->decimal('target_value', 10, 2);
            $table->decimal('actual_value', 10, 2)->nullable();
            $table->decimal('min_value', 10, 2)->nullable();  // untuk validation
            $table->decimal('max_value', 10, 2)->nullable();
            $table->boolean('is_critical')->default(false);  // parameter kritis
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operation_parameters');
    }
};

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
        Schema::create('batch_operation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_flow_id')->constrained()->onDelete('cascade');
            $table->foreignId('division_operation_id')->constrained('division_operations');
            
            // Time tracking
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->decimal('duration_minutes', 10, 2)->nullable();
            
            // Operator & Resources
            $table->string('operator_id')->nullable();      // NIK operator
            $table->string('machine_code')->nullable();     // Mesin yang digunakan
            $table->string('tooling_no')->nullable();       // Tooling number
            
            // Production Data
            $table->integer('processed_qty')->nullable();   // Qty diproses
            $table->integer('good_qty')->nullable();        // Qty baik
            $table->integer('rework_qty')->nullable();      // Qty rework
            $table->integer('scrap_qty')->nullable();       // Qty scrap
            
            // Parameters Actual (JSON)
            $table->json('actual_parameters')->nullable();  // Parameter aktual
            
            // Status & Approval
            $table->enum('status', ['pending', 'running', 'completed', 'hold'])->default('pending');
            $table->string('completed_by')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['batch_flow_id', 'division_operation_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_operation_logs');
    }
};

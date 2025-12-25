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
        Schema::create('batch_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('batches')->onDelete('cascade');
            $table->foreignId('operation_id')->constrained('operations')->onDelete('cascade');
            $table->foreignId('machine_id')->nullable()->constrained('machines')->onDelete('set null');
            $table->integer('sequence_order')->default(0);
            $table->integer('estimated_duration_minutes')->default(0);
            $table->enum('status', [
                'pending', 
                'ready', 
                'waiting_machine', 
                'in_progress', 
                'qc_pending', 
                'qc_passed', 
                'qc_failed', 
                'completed', 
                'on_hold', 
                'rework'
            ])->default('pending');
            $table->timestamp('estimated_start_at')->nullable();
            $table->timestamp('estimated_completion_at')->nullable();
            $table->timestamp('actual_start_at')->nullable();
            $table->timestamp('actual_completion_at')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->text('paused_reason')->nullable();
            $table->timestamp('resumed_at')->nullable();
            $table->foreignId('operator_id')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('actual_good_quantity')->default(0);
            $table->integer('actual_reject_quantity')->default(0);
            $table->text('operator_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_operations');
    }
};

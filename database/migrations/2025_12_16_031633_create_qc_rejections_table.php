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
        Schema::create('qc_rejections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quality_check_id')->constrained('quality_checks')->onDelete('cascade');
            $table->foreignId('batch_operation_id')->constrained('batch_operations')->onDelete('cascade');
            $table->integer('rejected_quantity');
            $table->integer('total_quantity');
            $table->text('reject_reason');
            $table->enum('action_taken', ['rework', 'scrap', 'use_as_is'])->nullable();
            $table->foreignId('rework_assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('rework_deadline')->nullable();
            $table->enum('rework_status', ['pending', 'in_progress', 'completed'])->nullable();
            $table->timestamp('rework_completed_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qc_rejections');
    }
};

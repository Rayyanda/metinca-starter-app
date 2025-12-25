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
        Schema::create('operation_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_operation_id')->constrained('batch_operations')->onDelete('cascade');
            $table->enum('action', [
                'started', 
                'paused', 
                'resumed', 
                'completed', 
                'qc_checked', 
                'rejected', 
                'reworked', 
                'machine_assigned',
                'approved'
            ]);
            $table->string('previous_status')->nullable();
            $table->string('new_status')->nullable();
            $table->foreignId('action_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('action_at');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operation_histories');
    }
};

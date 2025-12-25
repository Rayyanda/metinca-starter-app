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
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number')->unique();
            $table->foreignId('po_internal_id')->constrained('po_internals')->onDelete('cascade');
            $table->integer('quantity');
            $table->tinyInteger('priority')->default(1); // 1=normal, 2=high, 3=urgent
            $table->boolean('is_rush_order')->default(false);
            $table->enum('status', [
                'draft', 
                'pending_approval', 
                'approved', 
                'released', 
                'in_progress', 
                'completed', 
                'on_hold', 
                'rejected', 
                'cancelled'
            ])->default('draft');
            $table->foreignId('current_operation_id')->nullable()->constrained('operations')->onDelete('set null');
            $table->timestamp('estimated_completion_at')->nullable();
            $table->timestamp('actual_completion_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};

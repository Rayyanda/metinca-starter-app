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
            $table->string('batch_no')->unique();
            $table->string('part_no');
            $table->string('description');
            $table->integer('quantity');
            $table->enum('overall_status', [
                'draft', 'in_progress', 'completed', 'cancelled', 'on_hold'
            ])->default('draft');
            
            // Tracking current position
            $table->foreignId('current_division_id')->nullable()->constrained('divisions');
            //$table->foreignId('current_operation_id')->nullable()->constrained('operations');
            
            // Timeline
            $table->timestamp('planned_start')->nullable();
            $table->timestamp('planned_finish')->nullable();
            $table->timestamp('actual_start')->nullable();
            $table->timestamp('actual_finish')->nullable();
            
            // Yield tracking per divisi (JSON)
            $table->json('division_yields')->nullable(); // {wax: 95%, mould: 90%, ...}
            
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

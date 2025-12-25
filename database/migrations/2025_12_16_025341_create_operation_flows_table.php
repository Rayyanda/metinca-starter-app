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
        Schema::create('operation_flows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_operation_id')->nullable()->constrained('operations')->onDelete('cascade');
            $table->foreignId('to_operation_id')->constrained('operations')->onDelete('cascade');
            $table->integer('sequence_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Unique constraint: tidak boleh ada flow yang sama
            $table->unique(['from_operation_id', 'to_operation_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operation_flows');
    }
};

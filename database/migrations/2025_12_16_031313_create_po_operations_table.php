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
        Schema::create('po_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_internal_id')->constrained('po_internals')->onDelete('cascade');
            $table->foreignId('operation_id')->constrained('operations')->onDelete('cascade');
            $table->integer('estimated_duration_minutes')->nullable();
            $table->integer('sequence_order')->default(0);
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
        Schema::dropIfExists('po_operations');
    }
};

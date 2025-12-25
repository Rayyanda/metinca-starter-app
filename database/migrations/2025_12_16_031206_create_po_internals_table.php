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
        Schema::create('po_internals', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->string('customer_name');
            $table->text('product_description')->nullable();
            $table->integer('quantity');
            $table->date('due_date')->nullable();
            $table->enum('status', ['draft', 'confirmed', 'in_production', 'completed', 'cancelled'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('po_internals');
    }
};

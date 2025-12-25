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
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained('divisions')->onDelete('cascade');
            $table->string('name');
            $table->string('code')->unique();
            $table->string('machine_type')->nullable();
            $table->enum('status', ['available', 'in_use', 'maintenance', 'breakdown'])->default('available');
            $table->integer('max_concurrent_operations')->default(1);
            $table->integer('current_operations')->default(0);
            $table->json('specifications')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machines');
    }
};

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
        Schema::create('machine_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained('machines')->onDelete('cascade');
            $table->foreignId('operation_id')->constrained('operations')->onDelete('cascade');
            $table->integer('estimated_duration_minutes')->default(0);
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->integer('setup_time_minutes')->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Unique constraint: 1 mesin hanya bisa punya 1 config untuk 1 operasi
            $table->unique(['machine_id', 'operation_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_operations');
    }
};

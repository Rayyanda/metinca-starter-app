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
        Schema::create('quality_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_operation_id')->constrained('batch_operations')->onDelete('cascade');
            $table->enum('check_type', ['before_start', 'after_complete', 'in_process']);
            $table->enum('result', ['pass', 'fail', 'conditional_pass']);
            $table->integer('checked_quantity')->default(0);
            $table->integer('passed_quantity')->default(0);
            $table->integer('failed_quantity')->default(0);
            $table->text('defect_description')->nullable();
            $table->text('corrective_action')->nullable();
            $table->foreignId('checked_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('checked_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_checks');
    }
};

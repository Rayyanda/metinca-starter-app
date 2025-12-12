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
        Schema::create('operation_timelogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('operation_id')->constrained()->onDelete('cascade');
            $table->enum('log_type', [
                'setup_start',
                'setup_end',
                'production_start',
                'production_end',
                'waiting_start',
                'waiting_end',
                'break_start',
                'break_end',
                'problem_start',
                'problem_end'
            ]);
            $table->timestamp('log_time');
            $table->string('logged_by');          // user_id atau system
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            $table->index(['operation_id', 'log_type', 'log_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operation_timelogs');
    }
};

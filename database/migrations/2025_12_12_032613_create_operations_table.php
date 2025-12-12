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
        Schema::create('operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('batches')->onDelete('cascade');
            $table->string('code')->nullable();           // Kode operasi standar
            $table->string('name');                       // Nama operasi
            $table->text('description')->nullable();      // Deskripsi detail
            $table->integer('sequence');                  // Urutan (1,2,3...)

            // 🔥 TIME ESTIMATE & ACTUAL
            $table->decimal('setup_time_est', 8, 2)->default(0);    // Menit
            $table->decimal('process_time_est', 8, 2)->default(0);  // Menit per unit
            $table->decimal('wait_time_est', 8, 2)->default(0);     // Menit

            $table->decimal('setup_time_act', 8, 2)->nullable();    // Actual
            $table->decimal('process_time_act', 8, 2)->nullable();  // Actual
            $table->decimal('wait_time_act', 8, 2)->nullable();     // Actual

            // 🔥 SCHEDULING
            $table->timestamp('planned_start')->nullable();
            $table->timestamp('planned_end')->nullable();
            $table->timestamp('actual_start')->nullable();
            $table->timestamp('actual_end')->nullable();

            // 🔥 PRODUCTION DATA
            $table->integer('input_qty')->nullable();      // Qty masuk operasi
            $table->integer('output_qty')->nullable();     // Qty hasil baik
            $table->integer('reject_qty')->nullable();     // Qty reject

            // 🔥 RESOURCES
            $table->string('machine_code')->nullable();    // Mesin digunakan
            $table->string('operator_id')->nullable();     // NIK Operator
            $table->string('supervisor_id')->nullable();   // Supervisor

            $table->enum('status', [
                'pending',
                'ready',
                'running',
                'completed',
                'hold',
                'problem'
            ])->default('pending');

            $table->text('notes')->nullable();
            $table->timestamps();

            // Index untuk performa
            $table->index(['batch_id', 'sequence']);
            $table->index('status');
            $table->index('planned_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operations');
    }
};

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
        Schema::create('batch_flows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('divisions')->onDelete('cascade');
            $table->foreignId('from_division_id')->nullable()->constrained('divisions');
            $table->foreignId('to_division_id')->constrained('divisions');
            
            // Status di divisi ini
            $table->enum('status', [
                'pending',      // Menunggu diproses
                'in_progress',  // Sedang diproses
                'completed',    // Selesai di divisi ini
                'waiting',      // Menunggu material/approval
                'hold',         // Ditahan
                'cancelled'     // Dibatalkan
            ])->default('pending');
            
            // Quantity tracking
            $table->integer('input_qty')->nullable();   // Qty masuk divisi
            $table->integer('output_qty')->nullable();  // Qty keluar divisi baik
            $table->integer('reject_qty')->nullable();  // Qty reject di divisi ini
            $table->decimal('yield', 5, 2)->nullable(); // Yield di divisi ini
            
            // Time tracking
            $table->timestamp('received_date')->nullable();    // Diterima di divisi
            $table->timestamp('start_date')->nullable();       // Mulai proses
            $table->timestamp('completion_date')->nullable();  // Selesai proses
            $table->timestamp('release_date')->nullable();     // Dilepas ke divisi berikutnya
            
            // Approval
            $table->string('completed_by')->nullable();        // NIK yang complete
            $table->string('approved_by')->nullable();         // Supervisor approve
            $table->timestamp('approved_at')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['batch_id', 'to_division_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_flows');
    }
};

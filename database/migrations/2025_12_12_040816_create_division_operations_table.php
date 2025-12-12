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
        Schema::create('division_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->constrained('divisions')->onDelete('cascade');
            $table->string('operation_code')->unique();  // WAX-001, MOULD-010, etc
            $table->string('operation_name');            // Wax Injection, Mould Prepare
            $table->integer('sequence');                 // Urutan dalam divisi
            $table->boolean('is_mandatory')->default(true); // Harus dikerjakan
            $table->boolean('requires_approval')->default(false); // Butuh approval QC
            
            // Time standards
            $table->decimal('setup_time', 8, 2)->default(0);    // menit
            $table->decimal('process_time', 8, 2)->default(0);  // menit per unit
            $table->decimal('wait_time', 8, 2)->default(0);     // menit
            
            // Skill requirements
            $table->string('required_skill')->nullable();       // Basic, Intermediate, Expert
            $table->string('machine_type')->nullable();         // Jenis mesin
            
            // Parameters template (JSON)
            $table->json('standard_parameters')->nullable();    // Parameter standar
            
            $table->timestamps();
            
            $table->unique(['division_id', 'sequence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('division_operations');
    }
};

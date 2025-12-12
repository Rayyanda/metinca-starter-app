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
        Schema::create('operation_templates', function (Blueprint $table) {
            $table->id();
            $table->string('part_family')->nullable();  // Group part sejenis
            $table->string('operation_code')->unique(); // OPR-001
            $table->string('operation_name');           // 'Wax Injection'
            $table->text('description')->nullable();
            
            // 🔥 STANDARD TIME ESTIMATES
            $table->decimal('std_setup_time', 8, 2)->default(0);    // menit
            $table->decimal('std_process_time', 8, 2)->default(0);  // menit per unit
            $table->decimal('std_wait_time', 8, 2)->default(0);     // menit
            
            // 🔥 STANDARD PARAMETERS (JSON)
            $table->json('standard_params')->nullable(); // {temp: 70, pressure: 22828}
            
            $table->string('machine_type')->nullable();  // Jenis mesin required
            $table->integer('skill_level')->default(1);  // Skill operator required (1-5)
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operation_templates');
    }
};

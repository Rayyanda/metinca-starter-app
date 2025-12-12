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
        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->string('division_code')->unique();  // WAX, MOULD, MELT, MACH
            $table->string('name');                     // Wax Injection Division
            $table->integer('sequence');                // Urutan proses: 1, 2, 3, ...
            $table->boolean('is_entry_point')->default(false); // Divisi pertama
            $table->boolean('is_exit_point')->default(false);  // Divisi terakhir
            $table->integer('lead_time_days')->default(1);     // Lead time standar
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('divisions');
    }
};

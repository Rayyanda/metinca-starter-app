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
        Schema::create('machine_downtimes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained('machines')->onDelete('cascade');
            $table->enum('downtime_type', ['maintenance', 'breakdown', 'calibration']);
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->text('reason')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->foreignId('reported_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('machine_downtimes');
    }
};

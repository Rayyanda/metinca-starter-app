<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name')->nullable();
            $table->string('department');
            $table->string('location')->nullable();
            $table->timestamps();
        });

        Schema::create('damage_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_code')->unique();
            $table->foreignId('machine_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('reported_by')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('assigned_technician_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('department');
            $table->string('location')->nullable();
            $table->string('section')->nullable();
            $table->string('damage_type');
            $table->string('damage_type_other')->nullable();
            $table->text('description');
            $table->enum('priority', ['low', 'medium', 'high', 'critical']);
            $table->enum('status', ['waiting', 'in_progress', 'done'])->default('waiting');
            $table->timestamp('reported_at');
            $table->date('target_completed_at');
            $table->timestamp('actual_completed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'priority']);
            $table->index(['department', 'location']);
            $table->index('target_completed_at');
        });

        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('damage_report_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->enum('type', ['before', 'after']);
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });

        Schema::create('report_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('damage_report_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_histories');
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('damage_reports');
        Schema::dropIfExists('machines');
    }
};

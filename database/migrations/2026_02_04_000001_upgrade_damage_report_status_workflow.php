<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Update the enum to support new statuses FIRST (before data migration)
        // Note: MySQL doesn't allow direct ENUM modification with data
        // We'll use ALTER TABLE with MODIFY COLUMN
        DB::statement("
            ALTER TABLE damage_reports
            MODIFY COLUMN status ENUM(
                'uploaded_by_operator',
                'received_by_foreman_waiting_manager',
                'approved_by_manager_waiting_technician',
                'on_fixing_progress',
                'done_fixing',
                'waiting',
                'in_progress',
                'done'
            ) DEFAULT 'uploaded_by_operator'
        ");

        // Step 2: Add new columns for tracking approval actors
        Schema::table('damage_reports', function (Blueprint $table) {
            $table->foreignId('received_by_foreman_id')
                  ->nullable()
                  ->after('assigned_technician_id')
                  ->constrained('users')
                  ->cascadeOnUpdate()
                  ->nullOnDelete();

            $table->foreignId('approved_by_manager_id')
                  ->nullable()
                  ->after('received_by_foreman_id')
                  ->constrained('users')
                  ->cascadeOnUpdate()
                  ->nullOnDelete();

            $table->timestamp('received_by_foreman_at')->nullable()->after('reported_at');
            $table->timestamp('approved_by_manager_at')->nullable()->after('received_by_foreman_at');
            $table->timestamp('started_fixing_at')->nullable()->after('approved_by_manager_at');
        });

        // Step 3: Migrate existing data to new statuses
        // Map old statuses to new workflow positions
        DB::table('damage_reports')
            ->where('status', 'waiting')
            ->update(['status' => 'uploaded_by_operator']);

        DB::table('damage_reports')
            ->where('status', 'in_progress')
            ->update(['status' => 'on_fixing_progress']);

        DB::table('damage_reports')
            ->where('status', 'done')
            ->update(['status' => 'done_fixing']);

        // Step 4: Create history records for migrated data
        $reports = DB::table('damage_reports')->get();
        foreach ($reports as $report) {
            DB::table('report_histories')->insert([
                'damage_report_id' => $report->id,
                'actor_id' => null,
                'action' => 'status_migration',
                'from_status' => null,
                'to_status' => $report->status,
                'notes' => 'Migrated to new 7-step workflow',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert status mapping
        DB::table('damage_reports')
            ->where('status', 'uploaded_by_operator')
            ->update(['status' => 'waiting']);

        DB::table('damage_reports')
            ->whereIn('status', ['received_by_foreman_waiting_manager', 'approved_by_manager_waiting_technician'])
            ->update(['status' => 'waiting']);

        DB::table('damage_reports')
            ->where('status', 'on_fixing_progress')
            ->update(['status' => 'in_progress']);

        DB::table('damage_reports')
            ->where('status', 'done_fixing')
            ->update(['status' => 'done']);

        // Restore original enum
        DB::statement("
            ALTER TABLE damage_reports
            MODIFY COLUMN status ENUM('waiting', 'in_progress', 'done') DEFAULT 'waiting'
        ");

        // Drop new columns
        Schema::table('damage_reports', function (Blueprint $table) {
            $table->dropForeign(['received_by_foreman_id']);
            $table->dropForeign(['approved_by_manager_id']);
            $table->dropColumn([
                'received_by_foreman_id',
                'approved_by_manager_id',
                'received_by_foreman_at',
                'approved_by_manager_at',
                'started_fixing_at',
            ]);
        });
    }
};

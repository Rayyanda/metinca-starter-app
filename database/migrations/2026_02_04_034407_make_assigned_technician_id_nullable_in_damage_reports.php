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
        Schema::table('damage_reports', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['assigned_technician_id']);

            // Make the column nullable
            $table->foreignId('assigned_technician_id')->nullable()->change();

            // Re-add the foreign key constraint
            $table->foreign('assigned_technician_id')
                  ->references('id')
                  ->on('users')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('damage_reports', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['assigned_technician_id']);

            // Make the column NOT nullable
            $table->foreignId('assigned_technician_id')->change();

            // Re-add the foreign key constraint
            $table->foreign('assigned_technician_id')
                  ->references('id')
                  ->on('users')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');
        });
    }
};

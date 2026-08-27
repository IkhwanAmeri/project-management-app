<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add composite indexes for the app's most frequent report queries.
     */
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->index(['project_id', 'status']);
            $table->index(['project_id', 'due_date']);
            $table->index(['assigned_to', 'status', 'due_date']);
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->index(['project_id', 'created_at']);
        });
    }

    /**
     * Remove the composite indexes.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'created_at']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['assigned_to', 'status', 'due_date']);
            $table->dropIndex(['project_id', 'due_date']);
            $table->dropIndex(['project_id', 'status']);
        });
    }
};

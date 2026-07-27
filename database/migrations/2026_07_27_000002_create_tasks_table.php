<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the tasks table and its project, user, and parent-task links.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_task_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority', ['Low', 'Medium', 'High', 'Critical'])->default('Medium');
            $table->enum('status', ['Todo', 'In Progress', 'Review', 'Completed', 'Cancelled'])->default('Todo');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->decimal('actual_hours', 8, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Remove the tasks table.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};

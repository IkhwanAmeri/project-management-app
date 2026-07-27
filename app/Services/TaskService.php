<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskService
{
    /**
     * Create a new task within a project for the authenticated creator.
     *
     * @param array<string, mixed> $attributes
     */
    public function create(Project $project, array $attributes, User $creator): Task
    {
        $attributes['created_by'] = $creator->id;
        $attributes['completed_at'] = $attributes['status'] === 'Completed' ? now() : null;

        return $project->tasks()->create($attributes);
    }

    /**
     * Create a task directly beneath an existing parent task.
     *
     * @param array<string, mixed> $attributes
     */
    public function createSubtask(Task $parentTask, array $attributes, User $creator): Task
    {
        $attributes['parent_task_id'] = $parentTask->id;

        return $this->create($parentTask->project, $attributes, $creator);
    }

    /**
     * Update a task and maintain its completion timestamp from its status.
     *
     * @param array<string, mixed> $attributes
     */
    public function update(Task $task, array $attributes): Task
    {
        $attributes['completed_at'] = $attributes['status'] === 'Completed'
            ? ($task->completed_at ?? now())
            : null;

        $task->update($attributes);

        return $task->refresh();
    }

    /**
     * Mark a task as completed and store the completion time.
     */
    public function complete(Task $task): Task
    {
        $task->update([
            'status' => 'Completed',
            'completed_at' => $task->completed_at ?? now(),
        ]);

        return $task->refresh();
    }

    /**
     * Soft-delete a task.
     */
    public function delete(Task $task): void
    {
        $task->delete();
    }
}

<?php

namespace App\Services;

use App\Events\TaskAssigned;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskService
{
    /**
     * Inject the service used to record task events.
     */
    public function __construct(private readonly ActivityService $activityService)
    {
    }

    /**
     * Create a new task within a project for the authenticated creator.
     *
     * @param array<string, mixed> $attributes
     */
    public function create(Project $project, array $attributes, User $creator): Task
    {
        $attributes['created_by'] = $creator->id;
        $attributes['completed_at'] = $attributes['status'] === 'Completed' ? now() : null;

        $task = $project->tasks()->create($attributes);

        $this->activityService->log(
            $creator,
            'task_created',
            $project,
            $task,
            'Created task',
        );

        $this->dispatchAssignmentNotification($task, $creator);

        return $task;
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
    public function update(Task $task, array $attributes, User $updatedBy): Task
    {
        $previousAssignee = $task->assigned_to;
        $previousStatus = $task->status;
        $attributes['completed_at'] = $attributes['status'] === 'Completed'
            ? ($task->completed_at ?? now())
            : null;

        $task->update($attributes);

        $task = $task->refresh();

        if ((int) $task->assigned_to !== (int) $previousAssignee) {
            $this->dispatchAssignmentNotification($task, $updatedBy);
        }

        if ($task->status !== $previousStatus) {
            $this->activityService->log(
                $updatedBy,
                'task_status_changed',
                $task->project,
                $task,
                "{$previousStatus} → {$task->status}",
                ['from' => $previousStatus, 'to' => $task->status],
            );
        }

        return $task;
    }

    /**
     * Mark a task as completed and store the completion time.
     */
    public function complete(Task $task, User $completedBy): Task
    {
        $previousStatus = $task->status;
        $task->update([
            'status' => 'Completed',
            'completed_at' => $task->completed_at ?? now(),
        ]);

        $task = $task->refresh();

        if ($task->status !== $previousStatus) {
            $this->activityService->log(
                $completedBy,
                'task_status_changed',
                $task->project,
                $task,
                "{$previousStatus} → Completed",
                ['from' => $previousStatus, 'to' => 'Completed'],
            );
        }

        return $task;
    }

    /**
     * Soft-delete a task.
     */
    public function delete(Task $task, User $deletedBy): void
    {
        $this->activityService->log(
            $deletedBy,
            'task_deleted',
            $task->project,
            $task,
            'Deleted task',
            ['title' => $task->title],
        );

        $task->delete();
    }

    /**
     * Dispatch an assignment event when the task has an assignee.
     */
    private function dispatchAssignmentNotification(Task $task, User $assignedBy): void
    {
        if ($task->assigned_to !== null && (int) $task->assigned_to !== $assignedBy->id) {
            TaskAssigned::dispatch($task, $assignedBy);
        }
    }
}

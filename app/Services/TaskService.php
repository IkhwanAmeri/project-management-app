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

        $subtask = $this->create($parentTask->project, $attributes, $creator);

        $this->activityService->log(
            $creator,
            'subtask_created',
            $parentTask->project,
            $subtask,
            'Created subtask',
            ['parent_task_id' => $parentTask->id],
        );

        return $subtask;
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
        $previousPriority = $task->priority;

        $currentStatus = $attributes['status'] ?? $task->status;
        $attributes['completed_at'] = $currentStatus === 'Completed'
            ? ($task->completed_at ?? now())
            : null;

        $task->update($attributes);

        $task = $task->refresh();

        if ((int) $task->assigned_to !== (int) $previousAssignee) {
            $this->activityService->log(
                $updatedBy,
                'task_assignee_changed',
                $task->project,
                $task,
                'Assigned task to ' . ($task->assignedUser?->name ?? 'Unassigned'),
                ['from' => $previousAssignee, 'to' => $task->assigned_to],
            );
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

        if ($task->priority !== $previousPriority) {
            $this->activityService->log(
                $updatedBy,
                'task_priority_changed',
                $task->project,
                $task,
                "{$previousPriority} → {$task->priority}",
                ['from' => $previousPriority, 'to' => $task->priority],
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
     * Duplicate a task with copied fields and reset status.
     */
    public function duplicate(Task $original, User $creator): Task
    {
        $attributes = $original->only([
            'project_id',
            'title',
            'description',
            'priority',
            'assigned_to',
            'start_date',
            'due_date',
            'estimated_hours',
            'parent_task_id',
        ]);

        $attributes['title'] = $original->title.' (Copy)';
        $attributes['status'] = 'Todo';
        $attributes['completed_at'] = null;
        $attributes['actual_hours'] = null;
        $attributes['created_by'] = $creator->id;

        $project = $original->project;
        $task = $project->tasks()->create($attributes);

        $this->activityService->log(
            $creator,
            'task_duplicated',
            $project,
            $task,
            "Duplicated from: {$original->title}",
            ['original_task_id' => $original->id],
        );

        $this->dispatchAssignmentNotification($task, $creator);

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

<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification
{
    use Queueable;

    /**
     * Create a notification for a task assignment.
     */
    public function __construct(
        public readonly Task $task,
        public readonly User $assignedBy,
    )
    {
    }

    /**
     * Deliver this notification through Laravel's database channel.
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Define the task assignment data stored in the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'project_id' => $this->task->project_id,
            'title' => $this->task->title,
            'assigned_by' => $this->assignedBy->id,
            'message' => "{$this->assignedBy->name} assigned you to task: {$this->task->title}",
        ];
    }
}

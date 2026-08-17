<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskCompletedNotification extends Notification
{
    use Queueable;

    /**
     * Create a notification for a completed task.
     */
    public function __construct(public readonly Task $task)
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
     * Define the task completion data stored in the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'project_id' => $this->task->project_id,
            'title' => $this->task->title,
            'message' => "Task completed: {$this->task->title}",
        ];
    }
}

<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskDueSoonNotification extends Notification
{
    use Queueable;

    /**
     * Create a notification reminding the assignee about an upcoming deadline.
     */
    public function __construct(public readonly Task $task) {}

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
     * Define the due-soon reminder data stored in the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'project_id' => $this->task->project_id,
            'title' => $this->task->title,
            'due_date' => $this->task->due_date?->toDateString(),
            'message' => "Your task \"{$this->task->title}\" is due tomorrow ({$this->task->due_date?->format('M j')}).",
        ];
    }
}

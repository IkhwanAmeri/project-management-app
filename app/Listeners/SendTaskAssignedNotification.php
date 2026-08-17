<?php

namespace App\Listeners;

use App\Events\TaskAssigned;
use App\Notifications\TaskAssignedNotification;

class SendTaskAssignedNotification
{
    /**
     * Notify the assigned user when a task is assigned to them.
     */
    public function handle(TaskAssigned $event): void
    {
        $assignee = $event->task->assignedUser;

        if ($assignee !== null) {
            $assignee->notify(new TaskAssignedNotification($event->task, $event->assignedBy));
        }
    }
}

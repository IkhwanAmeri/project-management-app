<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\TaskDueSoonNotification;
use Illuminate\Console\Command;

class NotifyTaskDueSoonCommand extends Command
{
    protected $signature = 'tasks:notify-due-soon';

    protected $description = 'Notify assignees about tasks whose deadline is tomorrow';

    public function handle(): int
    {
        $dueDate = now()->addDay()->toDateString();

        $tasks = Task::query()
            ->with('assignedUser')
            ->whereNotNull('assigned_to')
            ->whereIn('status', ['Todo', 'In Progress', 'Review'])
            ->whereDate('due_date', $dueDate)
            ->get();

        $notified = 0;

        foreach ($tasks as $task) {
            $assignee = $task->assignedUser;

            if ($assignee === null) {
                continue;
            }

            $alreadyNotified = $assignee->notifications()
                ->where('type', TaskDueSoonNotification::class)
                ->where('data->task_id', $task->id)
                ->where('data->due_date', $task->due_date->toDateString())
                ->exists();

            if ($alreadyNotified) {
                continue;
            }

            $assignee->notify(new TaskDueSoonNotification($task));
            $notified++;
        }

        $this->info("Daily due-soon reminder checked tasks due on {$dueDate}; sent {$notified} reminders.");

        return self::SUCCESS;
    }
}

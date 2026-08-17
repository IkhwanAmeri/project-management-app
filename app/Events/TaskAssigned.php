<?php

namespace App\Events;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaskAssigned
{
    use Dispatchable, SerializesModels;

    /**
     * Create an event containing the task and user who assigned it.
     */
    public function __construct(
        public readonly Task $task,
        public readonly User $assignedBy,
    ) {
    }
}

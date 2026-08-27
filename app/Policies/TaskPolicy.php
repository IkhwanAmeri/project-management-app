<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * A user may view a task when they hold any role in the parent project.
     */
    public function view(User $user, Task $task): bool
    {
        return $user->hasProjectRole($task->project, 'Owner', 'Manager', 'Member');
    }

    /**
     * Only owners and managers may update task details.
     */
    public function update(User $user, Task $task): bool
    {
        return $user->hasProjectRole($task->project, 'Owner', 'Manager');
    }

    /**
     * Only owners and managers may delete a task.
     */
    public function delete(User $user, Task $task): bool
    {
        return $user->hasProjectRole($task->project, 'Owner', 'Manager');
    }

    /**
     * Any project member may mark a task as completed.
     */
    public function complete(User $user, Task $task): bool
    {
        return $user->hasProjectRole($task->project, 'Owner', 'Manager', 'Member');
    }

    /**
     * Only owners and managers may change task status.
     */
    public function changeStatus(User $user, Task $task): bool
    {
        return $user->hasProjectRole($task->project, 'Owner', 'Manager');
    }

    /**
     * Only owners and managers may change task priority.
     */
    public function changePriority(User $user, Task $task): bool
    {
        return $user->hasProjectRole($task->project, 'Owner', 'Manager');
    }

    /**
     * Only owners and managers may assign tasks to users.
     */
    public function assign(User $user, Task $task): bool
    {
        return $user->hasProjectRole($task->project, 'Owner', 'Manager');
    }
}

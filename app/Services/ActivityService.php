<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class ActivityService
{
    /**
     * Record an action performed by a user on an optional project or task.
     *
     * @param array<string, mixed> $properties
     */
    public function log(
        User $user,
        string $action,
        ?Project $project = null,
        ?Task $task = null,
        ?string $description = null,
        array $properties = [],
    ): Activity {
        return Activity::query()->create([
            'user_id' => $user->id,
            'project_id' => $project?->id ?? $task?->project_id,
            'task_id' => $task?->id,
            'action' => $action,
            'description' => $description,
            'properties' => $properties,
        ]);
    }
}

<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes, User $creator): Project
    {
        return DB::transaction(function () use ($attributes, $creator): Project {
            $project = $creator->ownedProjects()->create($attributes);

            $project->members()->attach($creator->id, [
                'role' => 'Owner',
                'joined_at' => now(),
            ]);

            return $project;
        });
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(Project $project, array $attributes): Project
    {
        $project->update($attributes);

        return $project->refresh();
    }
}

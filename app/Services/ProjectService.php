<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectService
{
    /**
     * Create a project, then add its creator as the Owner member.
     *
     * @param array<string, mixed> $attributes
     */
    public function create(array $attributes, User $creator): Project
    {
        return DB::transaction(function () use ($attributes, $creator): Project {
            $members = $attributes['members'] ?? [];
            unset($attributes['members']);
            $attributes['slug'] = $this->uniqueSlug($attributes['name']);
            $project = $creator->ownedProjects()->create($attributes);

            $project->members()->attach($creator->id, [
                'role' => 'Owner',
                'joined_at' => now(),
            ]);
            $this->addMembers($project, $members, $creator->id);

            return $project;
        });
    }

    /**
     * Update a project's editable attributes and return its fresh state.
     *
     * @param array<string, mixed> $attributes
     */
    public function update(Project $project, array $attributes): Project
    {
        $members = $attributes['members'] ?? [];
        unset($attributes['members']);
        $project->update($attributes);
        $this->addMembers($project, $members, $project->created_by);

        return $project->refresh();
    }

    /**
     * Add selected users to a project without removing existing members.
     *
     * @param array<int, array{user_id: int, role: string}> $members
     */
    private function addMembers(Project $project, array $members, int $ownerId): void
    {
        $assignments = collect($members)
            ->reject(fn (array $member): bool => (int) $member['user_id'] === $ownerId)
            ->mapWithKeys(fn (array $member): array => [
                $member['user_id'] => ['role' => $member['role'], 'joined_at' => now()],
            ])
            ->all();

        if ($assignments !== []) {
            $project->members()->syncWithoutDetaching($assignments);
        }
    }

    /**
     * Build an unused URL-friendly slug from a project name.
     */
    private function uniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name) ?: 'project';
        $slug = $baseSlug;
        $suffix = 2;

        while (Project::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}

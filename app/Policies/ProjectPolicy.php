<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Any authenticated user may view the projects index (results are scoped in the controller).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * A user may view a project when they hold any role in it.
     */
    public function view(User $user, Project $project): bool
    {
        return $user->hasProjectRole($project, 'Owner', 'Manager', 'Member');
    }

    /**
     * Any authenticated user may create a project.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Only owners and managers may update project details.
     */
    public function update(User $user, Project $project): bool
    {
        return $user->hasProjectRole($project, 'Owner', 'Manager');
    }

    /**
     * Only the owner may delete a project.
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->hasProjectRole($project, 'Owner');
    }

    /**
     * Only owners and managers may add or remove project members.
     */
    public function manageMembers(User $user, Project $project): bool
    {
        return $user->hasProjectRole($project, 'Owner', 'Manager');
    }

    /**
     * Any project member may create tasks in this project.
     */
    public function createTask(User $user, Project $project): bool
    {
        return $user->hasProjectRole($project, 'Owner', 'Manager', 'Member');
    }
}

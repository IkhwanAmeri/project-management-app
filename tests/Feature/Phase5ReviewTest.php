<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\User;
use App\Services\ProjectService;
use App\Services\TaskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase5ReviewTest extends TestCase
{
    use RefreshDatabase;

    private function createProjectWithTask(): array
    {
        $owner = User::factory()->create();
        $project = $owner->ownedProjects()->create([
            'name' => 'Review Project',
            'slug' => 'review-project',
            'status' => 'active',
        ]);
        $project->members()->attach($owner->id, ['role' => 'Owner', 'joined_at' => now()]);

        return compact('owner', 'project');
    }

    public function test_unauthorized_user_cannot_view_another_projects_kanban(): void
    {
        ['owner' => $owner, 'project' => $project] = $this->createProjectWithTask();

        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->get(route('projects.kanban', $project));

        $response->assertForbidden();
    }

    public function test_task_filtering_by_status_works(): void
    {
        ['owner' => $owner, 'project' => $project] = $this->createProjectWithTask();

        $project->tasks()->create([
            'title' => 'Todo Task',
            'description' => 'Description',
            'priority' => 'Low',
            'status' => 'Todo',
            'created_by' => $owner->id,
        ]);

        $project->tasks()->create([
            'title' => 'In Progress Task',
            'description' => 'Description',
            'priority' => 'Medium',
            'status' => 'In Progress',
            'created_by' => $owner->id,
        ]);

        $response = $this->actingAs($owner)
            ->get(route('tasks.index', ['status' => 'Todo']));

        $response->assertOk();
        $response->assertSee('Todo Task');
        $response->assertDontSee('In Progress Task');
    }

    public function test_sorting_only_allows_approved_columns(): void
    {
        ['owner' => $owner, 'project' => $project] = $this->createProjectWithTask();

        $response = $this->actingAs($owner)
            ->get(route('tasks.index', ['sort' => 'created_at; DROP TABLE users']));

        $response->assertOk();
    }

    public function test_non_member_cannot_view_activity_from_another_project(): void
    {
        $owner = User::factory()->create();
        $project = app(ProjectService::class)->create([
            'name' => 'Secret Project',
            'status' => 'active',
        ], $owner);
        app(TaskService::class)->create($project, [
            'title' => 'Secret Task',
            'status' => 'Todo',
            'priority' => 'Medium',
        ], $owner);

        $activity = Activity::where('project_id', $project->id)->first();
        $this->assertNotNull($activity);

        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->get(route('activities.show', $activity));

        $response->assertForbidden();
    }
}

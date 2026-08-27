<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KanbanDragDropTest extends TestCase
{
    use RefreshDatabase;

    private function createProjectWithTask(): array
    {
        $owner = User::factory()->create();
        $project = $owner->ownedProjects()->create([
            'name' => 'Kanban Project',
            'slug' => 'kanban-project',
            'status' => 'active',
        ]);
        $project->members()->attach($owner->id, ['role' => 'Owner', 'joined_at' => now()]);
        $task = $project->tasks()->create([
            'title' => 'Draggable Task',
            'description' => 'A task on the board',
            'priority' => 'High',
            'status' => 'Todo',
            'created_by' => $owner->id,
        ]);

        return compact('owner', 'project', 'task');
    }

    public function test_manager_can_update_status_via_json(): void
    {
        ['owner' => $owner, 'project' => $project, 'task' => $task] = $this->createProjectWithTask();

        $response = $this->actingAs($owner)
            ->patchJson(route('projects.tasks.status', ['project' => $project, 'task' => $task]), [
                'status' => 'In Progress',
            ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Task status updated successfully.',
            ])
            ->assertJsonPath('task.status', 'In Progress')
            ->assertJsonPath('task.id', $task->id);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'In Progress',
        ]);
    }

    public function test_invalid_status_returns_json_error(): void
    {
        ['owner' => $owner, 'project' => $project, 'task' => $task] = $this->createProjectWithTask();

        $response = $this->actingAs($owner)
            ->patchJson(route('projects.tasks.status', ['project' => $project, 'task' => $task]), [
                'status' => 'Garbage',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    public function test_non_member_cannot_update_status(): void
    {
        ['project' => $project, 'task' => $task] = $this->createProjectWithTask();
        $nonMember = User::factory()->create();

        $response = $this->actingAs($nonMember)
            ->patchJson(route('projects.tasks.status', ['project' => $project, 'task' => $task]), [
                'status' => 'Completed',
            ]);

        $response->assertForbidden();
    }

    public function test_member_role_cannot_update_status(): void
    {
        ['project' => $project, 'task' => $task] = $this->createProjectWithTask();
        $member = User::factory()->create();
        $project->members()->attach($member->id, ['role' => 'Member', 'joined_at' => now()]);

        $response = $this->actingAs($member)
            ->patchJson(route('projects.tasks.status', ['project' => $project, 'task' => $task]), [
                'status' => 'Completed',
            ]);

        $response->assertForbidden();
    }

    public function test_task_from_other_project_is_rejected(): void
    {
        ['owner' => $owner, 'project' => $project] = $this->createProjectWithTask();

        $otherProject = $owner->ownedProjects()->create([
            'name' => 'Other Project',
            'slug' => 'other-project',
            'status' => 'active',
        ]);
        $otherProject->members()->attach($owner->id, ['role' => 'Owner', 'joined_at' => now()]);
        $otherTask = $otherProject->tasks()->create([
            'title' => 'Other Task',
            'status' => 'Todo',
            'created_by' => $owner->id,
        ]);

        $response = $this->actingAs($owner)
            ->patchJson(route('projects.tasks.status', ['project' => $project, 'task' => $otherTask]), [
                'status' => 'Completed',
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    public function test_missing_status_field_returns_validation_error(): void
    {
        ['owner' => $owner, 'project' => $project, 'task' => $task] = $this->createProjectWithTask();

        $response = $this->actingAs($owner)
            ->patchJson(route('projects.tasks.status', ['project' => $project, 'task' => $task]), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }

    public function test_all_allowed_statuses_are_accepted(): void
    {
        ['owner' => $owner, 'project' => $project, 'task' => $task] = $this->createProjectWithTask();

        foreach (['Todo', 'In Progress', 'Review', 'Completed', 'Cancelled'] as $status) {
            $response = $this->actingAs($owner)
                ->patchJson(route('projects.tasks.status', ['project' => $project, 'task' => $task]), [
                    'status' => $status,
                ]);

            $response->assertOk();
            $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => $status]);
        }
    }

    public function test_unauthenticated_user_cannot_update_status(): void
    {
        ['project' => $project, 'task' => $task] = $this->createProjectWithTask();

        $response = $this->patchJson(route('projects.tasks.status', ['project' => $project, 'task' => $task]), [
            'status' => 'Completed',
        ]);

        $response->assertUnauthorized();
    }

    public function test_kanban_board_renders_draggable_cards(): void
    {
        ['owner' => $owner, 'project' => $project, 'task' => $task] = $this->createProjectWithTask();

        $response = $this->actingAs($owner)
            ->get(route('projects.kanban', $project));

        $response->assertOk()
            ->assertSee('Draggable Task')
            ->assertSee('draggable')
            ->assertSee('handleDragStart')
            ->assertSee('handleDrop');
    }

    public function test_completed_at_derived_when_moving_to_completed(): void
    {
        ['owner' => $owner, 'project' => $project, 'task' => $task] = $this->createProjectWithTask();

        $this->assertNull($task->completed_at);

        $response = $this->actingAs($owner)
            ->patchJson(route('projects.tasks.status', ['project' => $project, 'task' => $task]), [
                'status' => 'Completed',
            ]);

        $response->assertOk();
        $this->assertNotNull($task->refresh()->completed_at);
    }

    public function test_cleared_when_moving_away_from_completed(): void
    {
        ['owner' => $owner, 'project' => $project, 'task' => $task] = $this->createProjectWithTask();

        $task->update(['status' => 'Completed', 'completed_at' => now()]);

        $response = $this->actingAs($owner)
            ->patchJson(route('projects.tasks.status', ['project' => $project, 'task' => $task]), [
                'status' => 'In Progress',
            ]);

        $response->assertOk();
        $this->assertNull($task->refresh()->completed_at);
    }
}

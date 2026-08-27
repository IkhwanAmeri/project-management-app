<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskQuickActionTest extends TestCase
{
    use RefreshDatabase;

    private function createProjectWithTask(): array
    {
        $owner = User::factory()->create();
        $project = $owner->ownedProjects()->create([
            'name' => 'Quick Action Project',
            'slug' => 'quick-action-project',
            'status' => 'active',
        ]);
        $project->members()->attach($owner->id, ['role' => 'Owner', 'joined_at' => now()]);
        $task = $project->tasks()->create([
            'title' => 'Original Task',
            'description' => 'Task description',
            'priority' => 'High',
            'status' => 'In Progress',
            'created_by' => $owner->id,
            'assigned_to' => $owner->id,
        ]);

        return compact('owner', 'project', 'task');
    }

    public function test_member_can_duplicate_a_task(): void
    {
        ['owner' => $owner, 'task' => $task] = $this->createProjectWithTask();

        $response = $this->actingAs($owner)
            ->post(route('tasks.duplicate', $task));

        $response->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'title' => 'Original Task (Copy)',
            'project_id' => $task->project_id,
            'status' => 'Todo',
            'priority' => 'High',
            'assigned_to' => $owner->id,
            'completed_at' => null,
            'actual_hours' => null,
        ]);

        $this->assertDatabaseHas('activities', [
            'action' => 'task_duplicated',
            'task_id' => Task::where('title', 'Original Task (Copy)')->first()->id,
        ]);
    }

    public function test_non_member_cannot_duplicate_task(): void
    {
        ['task' => $task] = $this->createProjectWithTask();
        $nonMember = User::factory()->create();

        $response = $this->actingAs($nonMember)
            ->post(route('tasks.duplicate', $task));

        $response->assertForbidden();
    }

    public function test_manager_can_change_status(): void
    {
        ['owner' => $owner, 'task' => $task] = $this->createProjectWithTask();

        $response = $this->actingAs($owner)
            ->patch(route('tasks.status', $task), ['status' => 'Completed']);

        $response->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'Completed',
        ]);
    }

    public function test_invalid_status_is_rejected(): void
    {
        ['owner' => $owner, 'task' => $task] = $this->createProjectWithTask();

        $response = $this->actingAs($owner)
            ->patch(route('tasks.status', $task), ['status' => 'InvalidStatus']);

        $response->assertSessionHasErrors('status');
    }

    public function test_member_cannot_change_status(): void
    {
        ['task' => $task] = $this->createProjectWithTask();
        $member = User::factory()->create();
        $task->project->members()->attach($member->id, ['role' => 'Member', 'joined_at' => now()]);

        $response = $this->actingAs($member)
            ->patch(route('tasks.status', $task), ['status' => 'Completed']);

        $response->assertForbidden();
    }

    public function test_manager_can_change_priority(): void
    {
        ['owner' => $owner, 'task' => $task] = $this->createProjectWithTask();

        $response = $this->actingAs($owner)
            ->patch(route('tasks.priority', $task), ['priority' => 'Critical']);

        $response->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'priority' => 'Critical',
        ]);

        $this->assertDatabaseHas('activities', [
            'action' => 'task_priority_changed',
            'task_id' => $task->id,
        ]);
    }

    public function test_invalid_priority_is_rejected(): void
    {
        ['owner' => $owner, 'task' => $task] = $this->createProjectWithTask();

        $response = $this->actingAs($owner)
            ->patch(route('tasks.priority', $task), ['priority' => 'MegaCritical']);

        $response->assertSessionHasErrors('priority');
    }

    public function test_manager_can_assign_task_to_member(): void
    {
        ['owner' => $owner, 'task' => $task, 'project' => $project] = $this->createProjectWithTask();
        $member = User::factory()->create();
        $project->members()->attach($member->id, ['role' => 'Member', 'joined_at' => now()]);

        $response = $this->actingAs($owner)
            ->patch(route('tasks.assign', $task), ['assigned_to' => $member->id]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'assigned_to' => $member->id,
        ]);
    }

    public function test_cannot_assign_to_non_member(): void
    {
        ['owner' => $owner, 'task' => $task] = $this->createProjectWithTask();
        $outsider = User::factory()->create();

        $response = $this->actingAs($owner)
            ->patch(route('tasks.assign', $task), ['assigned_to' => $outsider->id]);

        $response->assertSessionHasErrors('assigned_to');
    }

    public function test_manager_can_unassign_task(): void
    {
        ['owner' => $owner, 'task' => $task] = $this->createProjectWithTask();

        $response = $this->actingAs($owner)
            ->patch(route('tasks.assign', $task), ['assigned_to' => '']);

        $response->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'assigned_to' => null,
        ]);
    }

    public function test_show_page_displays_quick_actions(): void
    {
        ['owner' => $owner, 'task' => $task] = $this->createProjectWithTask();

        $response = $this->actingAs($owner)
            ->get(route('tasks.show', $task));

        $response->assertOk();
        $response->assertSee('Duplicate');
        $response->assertSee(route('tasks.duplicate', $task));
        $response->assertSee(route('tasks.status', $task));
        $response->assertSee(route('tasks.priority', $task));
        $response->assertSee(route('tasks.assign', $task));
    }
}

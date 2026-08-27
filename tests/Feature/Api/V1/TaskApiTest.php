<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    private function createProjectWithTask(): array
    {
        $owner = User::factory()->create();
        $project = $owner->ownedProjects()->create([
            'name' => 'Task API Project',
            'slug' => 'task-api-project',
            'status' => 'active',
        ]);
        $project->members()->attach($owner->id, ['role' => 'Owner', 'joined_at' => now()]);
        $task = $project->tasks()->create([
            'title' => 'Existing Task',
            'description' => 'Task description',
            'priority' => 'High',
            'status' => 'Todo',
            'created_by' => $owner->id,
        ]);

        return compact('owner', 'project', 'task');
    }

    public function test_unauthenticated_user_cannot_access_tasks(): void
    {
        ['task' => $task] = $this->createProjectWithTask();

        $response = $this->getJson("/api/v1/tasks/{$task->id}");
        $response->assertUnauthorized();
    }

    public function test_member_can_list_project_tasks(): void
    {
        ['owner' => $owner, 'project' => $project] = $this->createProjectWithTask();
        Sanctum::actingAs($owner);

        $response = $this->getJson("/api/v1/projects/{$project->id}/tasks");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Existing Task');
    }

    public function test_non_member_cannot_list_project_tasks(): void
    {
        ['project' => $project] = $this->createProjectWithTask();
        $other = User::factory()->create();
        Sanctum::actingAs($other);

        $response = $this->getJson("/api/v1/projects/{$project->id}/tasks");

        $response->assertForbidden();
    }

    public function test_member_can_create_task(): void
    {
        ['owner' => $owner, 'project' => $project] = $this->createProjectWithTask();
        Sanctum::actingAs($owner);

        $response = $this->postJson("/api/v1/projects/{$project->id}/tasks", [
            'title' => 'New API Task',
            'priority' => 'Medium',
            'status' => 'Todo',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'New API Task')
            ->assertJsonPath('data.priority', 'Medium')
            ->assertJsonPath('data.status', 'Todo')
            ->assertJsonStructure([
                'data' => ['id', 'title', 'project_id', 'assigned_user', 'creator'],
            ]);
    }

    public function test_create_task_requires_title_priority_status(): void
    {
        ['owner' => $owner, 'project' => $project] = $this->createProjectWithTask();
        Sanctum::actingAs($owner);

        $response = $this->postJson("/api/v1/projects/{$project->id}/tasks", []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'priority', 'status']);
    }

    public function test_non_member_cannot_create_task(): void
    {
        ['project' => $project] = $this->createProjectWithTask();
        $other = User::factory()->create();
        Sanctum::actingAs($other);

        $response = $this->postJson("/api/v1/projects/{$project->id}/tasks", [
            'title' => 'Hack Task',
            'priority' => 'Low',
            'status' => 'Todo',
        ]);

        $response->assertForbidden();
    }

    public function test_member_can_view_task(): void
    {
        ['owner' => $owner, 'task' => $task] = $this->createProjectWithTask();
        Sanctum::actingAs($owner);

        $response = $this->getJson("/api/v1/tasks/{$task->id}");

        $response->assertOk()
            ->assertJsonPath('data.title', 'Existing Task')
            ->assertJsonPath('data.priority', 'High')
            ->assertJsonStructure([
                'data' => ['id', 'title', 'project', 'assigned_user', 'creator', 'subtasks'],
            ]);
    }

    public function test_non_member_cannot_view_task(): void
    {
        ['task' => $task] = $this->createProjectWithTask();
        $other = User::factory()->create();
        Sanctum::actingAs($other);

        $response = $this->getJson("/api/v1/tasks/{$task->id}");

        $response->assertForbidden();
    }

    public function test_nonexistent_task_returns_404(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/tasks/9999');

        $response->assertNotFound();
    }

    public function test_owner_can_update_task(): void
    {
        ['owner' => $owner, 'task' => $task] = $this->createProjectWithTask();
        Sanctum::actingAs($owner);

        $response = $this->putJson("/api/v1/tasks/{$task->id}", [
            'title' => 'Updated Task',
            'priority' => 'Critical',
            'status' => 'In Progress',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Updated Task')
            ->assertJsonPath('data.priority', 'Critical')
            ->assertJsonPath('data.status', 'In Progress');
    }

    public function test_member_cannot_update_task(): void
    {
        ['owner' => $owner, 'project' => $project, 'task' => $task] = $this->createProjectWithTask();
        $member = User::factory()->create();
        $project->members()->attach($member->id, ['role' => 'Member', 'joined_at' => now()]);
        Sanctum::actingAs($member);

        $response = $this->putJson("/api/v1/tasks/{$task->id}", [
            'title' => 'Hacked',
            'priority' => 'Low',
            'status' => 'Todo',
        ]);

        $response->assertForbidden();
    }

    public function test_owner_can_delete_task(): void
    {
        ['owner' => $owner, 'task' => $task] = $this->createProjectWithTask();
        Sanctum::actingAs($owner);

        $response = $this->deleteJson("/api/v1/tasks/{$task->id}");

        $response->assertNoContent();
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    public function test_member_cannot_delete_task(): void
    {
        ['owner' => $owner, 'project' => $project, 'task' => $task] = $this->createProjectWithTask();
        $member = User::factory()->create();
        $project->members()->attach($member->id, ['role' => 'Member', 'joined_at' => now()]);
        Sanctum::actingAs($member);

        $response = $this->deleteJson("/api/v1/tasks/{$task->id}");

        $response->assertForbidden();
    }

    public function test_tasks_index_returns_paginated_structure(): void
    {
        ['owner' => $owner, 'project' => $project] = $this->createProjectWithTask();
        Sanctum::actingAs($owner);

        $response = $this->getJson("/api/v1/projects/{$project->id}/tasks");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    ['id', 'title', 'status', 'priority'],
                ],
            ]);
    }

    public function test_tasks_index_filters_by_status(): void
    {
        ['owner' => $owner, 'project' => $project] = $this->createProjectWithTask();
        $project->tasks()->create([
            'title' => 'Completed Task',
            'priority' => 'Low',
            'status' => 'Completed',
            'created_by' => $owner->id,
        ]);
        Sanctum::actingAs($owner);

        $response = $this->getJson("/api/v1/projects/{$project->id}/tasks?status=Todo");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Existing Task');
    }

    public function test_tasks_index_filters_by_priority(): void
    {
        ['owner' => $owner, 'project' => $project] = $this->createProjectWithTask();
        $project->tasks()->create([
            'title' => 'Low Task',
            'priority' => 'Low',
            'status' => 'Todo',
            'created_by' => $owner->id,
        ]);
        Sanctum::actingAs($owner);

        $response = $this->getJson("/api/v1/projects/{$project->id}/tasks?priority=High");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Existing Task');
    }

    public function test_tasks_index_searches_by_title(): void
    {
        ['owner' => $owner, 'project' => $project] = $this->createProjectWithTask();
        $project->tasks()->create([
            'title' => 'Another Task',
            'priority' => 'Low',
            'status' => 'Todo',
            'created_by' => $owner->id,
        ]);
        Sanctum::actingAs($owner);

        $response = $this->getJson("/api/v1/projects/{$project->id}/tasks?search=Existing");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Existing Task');
    }

    public function test_completed_at_derived_on_status_change(): void
    {
        ['owner' => $owner, 'task' => $task] = $this->createProjectWithTask();
        Sanctum::actingAs($owner);

        $this->putJson("/api/v1/tasks/{$task->id}", [
            'title' => 'Existing Task',
            'priority' => 'High',
            'status' => 'Completed',
        ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'Completed',
        ]);
        $this->assertNotNull($task->fresh()->completed_at);
    }

    public function test_response_exposes_no_sensitive_fields(): void
    {
        ['owner' => $owner, 'task' => $task] = $this->createProjectWithTask();
        Sanctum::actingAs($owner);

        $response = $this->getJson("/api/v1/tasks/{$task->id}");
        $json = $response->json();

        $this->assertArrayNotHasKey('deleted_at', $json['data']);
        $this->assertArrayNotHasKey('updated_at', $json['data']);
    }
}

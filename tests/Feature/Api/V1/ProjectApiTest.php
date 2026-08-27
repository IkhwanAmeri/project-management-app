<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectApiTest extends TestCase
{
    use RefreshDatabase;

    private function createOwnerAndProject(): array
    {
        $owner = User::factory()->create();
        $project = $owner->ownedProjects()->create([
            'name' => 'API Project',
            'slug' => 'api-project',
            'status' => 'active',
        ]);
        $project->members()->attach($owner->id, ['role' => 'Owner', 'joined_at' => now()]);

        return compact('owner', 'project');
    }

    public function test_unauthenticated_user_cannot_access_projects(): void
    {
        $response = $this->getJson('/api/v1/projects');
        $response->assertUnauthorized();
    }

    public function test_member_can_list_projects(): void
    {
        ['owner' => $owner] = $this->createOwnerAndProject();
        Sanctum::actingAs($owner);

        $response = $this->getJson('/api/v1/projects');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'API Project');
    }

    public function test_non_member_does_not_see_other_projects(): void
    {
        $other = User::factory()->create();
        Sanctum::actingAs($other);

        $response = $this->getJson('/api/v1/projects');

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_any_user_can_create_project(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/projects', [
            'name' => 'New Project',
            'status' => 'planning',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.name', 'New Project')
            ->assertJsonPath('data.owner.id', $user->id)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'slug', 'status', 'owner', 'members_count'],
            ]);
    }

    public function test_create_project_requires_name(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/projects', [
            'status' => 'planning',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    }

    public function test_owner_can_view_project(): void
    {
        ['owner' => $owner, 'project' => $project] = $this->createOwnerAndProject();
        Sanctum::actingAs($owner);

        $response = $this->getJson("/api/v1/projects/{$project->id}");

        $response->assertOk()
            ->assertJsonPath('data.name', 'API Project')
            ->assertJsonStructure([
                'data' => ['id', 'name', 'slug', 'owner', 'members', 'tasks'],
            ]);
    }

    public function test_non_member_cannot_view_project(): void
    {
        ['project' => $project] = $this->createOwnerAndProject();
        $other = User::factory()->create();
        Sanctum::actingAs($other);

        $response = $this->getJson("/api/v1/projects/{$project->id}");

        $response->assertForbidden();
    }

    public function test_nonexistent_project_returns_404(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/projects/9999');

        $response->assertNotFound();
    }

    public function test_owner_can_update_project(): void
    {
        ['owner' => $owner, 'project' => $project] = $this->createOwnerAndProject();
        Sanctum::actingAs($owner);

        $response = $this->putJson("/api/v1/projects/{$project->id}", [
            'name' => 'Updated Project',
            'status' => 'completed',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Project')
            ->assertJsonPath('data.status', 'completed');
    }

    public function test_member_cannot_update_project(): void
    {
        ['owner' => $owner, 'project' => $project] = $this->createOwnerAndProject();
        $member = User::factory()->create();
        $project->members()->attach($member->id, ['role' => 'Member', 'joined_at' => now()]);
        Sanctum::actingAs($member);

        $response = $this->putJson("/api/v1/projects/{$project->id}", [
            'name' => 'Hacked',
            'status' => 'active',
        ]);

        $response->assertForbidden();
    }

    public function test_update_project_requires_name_and_status(): void
    {
        ['owner' => $owner, 'project' => $project] = $this->createOwnerAndProject();
        Sanctum::actingAs($owner);

        $response = $this->putJson("/api/v1/projects/{$project->id}", []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'status']);
    }

    public function test_owner_can_delete_project(): void
    {
        ['owner' => $owner, 'project' => $project] = $this->createOwnerAndProject();
        Sanctum::actingAs($owner);

        $response = $this->deleteJson("/api/v1/projects/{$project->id}");

        $response->assertNoContent();
        $this->assertSoftDeleted('projects', ['id' => $project->id]);
    }

    public function test_manager_cannot_delete_project(): void
    {
        ['owner' => $owner, 'project' => $project] = $this->createOwnerAndProject();
        $manager = User::factory()->create();
        $project->members()->attach($manager->id, ['role' => 'Manager', 'joined_at' => now()]);
        Sanctum::actingAs($manager);

        $response = $this->deleteJson("/api/v1/projects/{$project->id}");

        $response->assertForbidden();
    }

    public function test_index_returns_paginated_structure(): void
    {
        ['owner' => $owner] = $this->createOwnerAndProject();
        Sanctum::actingAs($owner);

        $response = $this->getJson('/api/v1/projects');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    ['id', 'name', 'slug', 'status', 'owner'],
                ],
            ]);
    }

    public function test_response_exposes_no_sensitive_fields(): void
    {
        ['owner' => $owner, 'project' => $project] = $this->createOwnerAndProject();
        Sanctum::actingAs($owner);

        $response = $this->getJson("/api/v1/projects/{$project->id}");
        $json = $response->json();

        $this->assertArrayNotHasKey('deleted_at', $json['data']);
        $this->assertArrayNotHasKey('created_by', $json['data']);
        $this->assertArrayNotHasKey('updated_at', $json['data']);
    }
}

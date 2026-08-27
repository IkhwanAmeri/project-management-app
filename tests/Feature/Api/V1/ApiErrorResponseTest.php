<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Services\ProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiErrorResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_error_returns_422_envelope(): void
    {
        $response = $this->postJson('/api/v1/login', []);

        $response->assertUnprocessable()
            ->assertExactJson([
                'message' => 'Validation failed.',
                'errors' => [
                    'email' => ['The email field is required.'],
                    'password' => ['The password field is required.'],
                ],
            ]);
    }

    public function test_validation_error_keeps_nested_field_errors(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/projects', [
            'name' => 'x',
            'members' => [
                ['user_id' => 9999, 'role' => 'Member'],
            ],
        ]);

        $response->assertUnprocessable()
            ->assertJsonPath('message', 'Validation failed.')
            ->assertJsonValidationErrors(['members.0.user_id']);
    }

    public function test_unauthenticated_api_request_returns_401_envelope(): void
    {
        $response = $this->getJson('/api/v1/projects');

        $response->assertUnauthorized()
            ->assertExactJson(['message' => 'Unauthenticated.']);
    }

    public function test_api_returns_json_401_even_without_accept_header(): void
    {
        $response = $this->get('/api/v1/projects');

        $response->assertUnauthorized()
            ->assertExactJson(['message' => 'Unauthenticated.']);
    }

    public function test_unauthorized_api_request_returns_403_envelope(): void
    {
        $owner = User::factory()->create();
        $project = $owner->ownedProjects()->create(['name' => 'P', 'slug' => 'p', 'status' => 'active']);
        $project->members()->attach($owner->id, ['role' => 'Owner', 'joined_at' => now()]);

        $other = User::factory()->create();
        Sanctum::actingAs($other);

        $response = $this->getJson("/api/v1/projects/{$project->id}");

        $response->assertForbidden()
            ->assertExactJson(['message' => 'You are not authorized to perform this action.']);
    }

    public function test_missing_resource_returns_404_envelope(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/projects/9999');

        $response->assertNotFound()
            ->assertExactJson(['message' => 'Resource not found.']);
    }

    public function test_missing_nested_task_returns_404_envelope(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/tasks/9999');

        $response->assertNotFound()
            ->assertExactJson(['message' => 'Resource not found.']);
    }

    public function test_server_error_returns_500_envelope_without_leaking_details(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $log = Log::spy();
        $this->app->instance('log', $log);

        $this->mock(ProjectService::class, function ($mock): void {
            $mock->shouldReceive('create')->andThrow(new \RuntimeException('sensitive internal detail'));
        });

        $response = $this->postJson('/api/v1/projects', ['name' => 'X']);

        $response->assertInternalServerError()
            ->assertExactJson(['message' => 'An unexpected error occurred.']);

        Log::shouldHaveReceived('error');
    }

    public function test_api_errors_keep_consistent_json_structure(): void
    {
        $owner = User::factory()->create();
        $project = $owner->ownedProjects()->create(['name' => 'P', 'slug' => 'p', 'status' => 'active']);
        $project->members()->attach($owner->id, ['role' => 'Owner', 'joined_at' => now()]);

        $other = User::factory()->create();
        Sanctum::actingAs($other);

        $forbidden = $this->getJson("/api/v1/projects/{$project->id}");
        $notFound = $this->getJson('/api/v1/projects/9999');

        foreach ([$forbidden, $notFound] as $response) {
            $keys = array_keys($response->json());
            $this->assertSame(['message'], $keys);
        }
    }

    public function test_web_404_keeps_html_error_page(): void
    {
        $response = $this->get('/this-web-route-does-not-exist');

        $response->assertNotFound();
        $this->assertStringContainsString('text/html', $response->headers->get('Content-Type'));
    }

    public function test_unauthenticated_web_user_still_redirects_to_login(): void
    {
        $owner = User::factory()->create();
        $project = $owner->ownedProjects()->create(['name' => 'P', 'slug' => 'p', 'status' => 'active']);
        $project->members()->attach($owner->id, ['role' => 'Owner', 'joined_at' => now()]);

        $response = $this->get(route('projects.show', $project));

        $response->assertRedirect(route('login'));
    }
}

<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use App\Notifications\TaskAssignedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskAssignmentNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_assignment_creates_database_notification_for_assignee(): void
    {
        $manager = User::factory()->create();
        $assignee = User::factory()->create();

        $project = $manager->ownedProjects()->create([
            'name' => 'Website Redesign',
            'slug' => 'website-redesign',
            'description' => 'Test project',
            'status' => 'active',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(7),
        ]);

        $project->members()->attach($manager->id, [
            'role' => 'Manager',
            'joined_at' => now(),
        ]);

        $project->members()->attach($assignee->id, [
            'role' => 'Member',
            'joined_at' => now(),
        ]);

        $this->actingAs($manager);

        $response = $this->post(route('projects.tasks.store', $project), [
            'title' => 'Build notification UI',
            'description' => 'Task assignment notification flow',
            'priority' => 'High',
            'status' => 'Todo',
            'assigned_to' => $assignee->id,
            'start_date' => now()->toDateString(),
            'due_date' => now()->addDay()->toDateString(),
            'estimated_hours' => 3,
            'actual_hours' => 0,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'type' => TaskAssignedNotification::class,
            'notifiable_type' => User::class,
            'notifiable_id' => $assignee->id,
        ]);
    }
}

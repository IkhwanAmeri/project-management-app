<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private function createProjectWithTasks(?User $owner = null): array
    {
        $owner ??= User::factory()->create();
        $member = User::factory()->create();

        $project = $owner->ownedProjects()->create([
            'name' => 'Report Project',
            'slug' => 'report-project',
            'status' => 'active',
        ]);
        $project->members()->attach($owner->id, ['role' => 'Owner', 'joined_at' => now()]);
        $project->members()->attach($member->id, ['role' => 'Member', 'joined_at' => now()]);

        $completed = $project->tasks()->create([
            'title' => 'Finished task',
            'priority' => 'High',
            'status' => 'Completed',
            'created_by' => $owner->id,
            'assigned_to' => $member->id,
            'completed_at' => now(),
            'estimated_hours' => 8,
            'actual_hours' => 6,
        ]);
        $open = $project->tasks()->create([
            'title' => 'Open task',
            'priority' => 'Medium',
            'status' => 'Todo',
            'created_by' => $owner->id,
            'assigned_to' => $member->id,
            'due_date' => now()->subDay(),
        ]);

        return compact('owner', 'member', 'project', 'completed', 'open');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('reports.index'))->assertRedirect(route('login'));
    }

    public function test_report_hub_lists_my_projects(): void
    {
        ['owner' => $owner, 'project' => $project] = $this->createProjectWithTasks();

        $this->actingAs($owner)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee('User Task Report')
            ->assertSee('Project Report')
            ->assertSee($project->name);
    }

    public function test_user_task_report_shows_statistics(): void
    {
        ['owner' => $owner, 'member' => $member] = $this->createProjectWithTasks();

        $response = $this->actingAs($member)
            ->get(route('reports.user-tasks'));

        $response->assertOk()
            ->assertSee('User Task Report')
            ->assertSee('Finished task')
            ->assertSee('Open task')
            ->assertSee('1')
            ->assertSee('Overdue');
    }

    public function test_user_task_report_respects_project_filter(): void
    {
        ['owner' => $owner, 'member' => $member] = $this->createProjectWithTasks();

        $otherProject = $owner->ownedProjects()->create(['name' => 'Other', 'slug' => 'other', 'status' => 'active']);
        $otherProject->members()->attach($owner->id, ['role' => 'Owner', 'joined_at' => now()]);
        $otherProject->members()->attach($member->id, ['role' => 'Member', 'joined_at' => now()]);
        $otherProject->tasks()->create([
            'title' => 'Other project task',
            'priority' => 'Low',
            'status' => 'Todo',
            'created_by' => $owner->id,
            'assigned_to' => $member->id,
        ]);

        $response = $this->actingAs($member)
            ->get(route('reports.user-tasks', ['project' => $otherProject->id]));

        $response->assertOk()
            ->assertSee('Other project task')
            ->assertDontSee('Finished task');
    }

    public function test_member_cannot_view_another_users_report(): void
    {
        ['owner' => $owner, 'member' => $member, 'project' => $project] = $this->createProjectWithTasks();

        $this->actingAs($member)
            ->get(route('reports.user-tasks', ['user' => $owner->id, 'project' => $project->id]))
            ->assertForbidden();
    }

    public function test_manager_can_view_any_members_report_in_their_project(): void
    {
        ['owner' => $owner, 'member' => $member, 'project' => $project] = $this->createProjectWithTasks();
        $manager = User::factory()->create();
        $project->members()->attach($manager->id, ['role' => 'Manager', 'joined_at' => now()]);

        $response = $this->actingAs($manager)
            ->get(route('reports.user-tasks', ['user' => $member->id, 'project' => $project->id]));

        $response->assertOk()
            ->assertSee($member->name)
            ->assertSee('Finished task');
    }

    public function test_non_member_is_forbidden_from_project_report(): void
    {
        ['owner' => $owner, 'project' => $project] = $this->createProjectWithTasks();
        $outsider = User::factory()->create();

        $this->actingAs($outsider)
            ->get(route('reports.projects.show', $project->id))
            ->assertForbidden();
    }

    public function test_project_report_shows_member_workload(): void
    {
        ['owner' => $owner, 'member' => $member, 'project' => $project] = $this->createProjectWithTasks();

        $response = $this->actingAs($owner)
            ->get(route('reports.projects.show', $project->id));

        $response->assertOk()
            ->assertSee('Member Workload')
            ->assertSee($member->name)
            ->assertSee('Finished task')
            ->assertSee('Open task');
    }

    public function test_user_task_report_csv_download(): void
    {
        ['member' => $member, 'project' => $project] = $this->createProjectWithTasks();

        $response = $this->actingAs($member)
            ->get(route('reports.user-tasks', ['project' => $project->id, 'format' => 'csv']));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('Finished task', $response->streamedContent());
    }

    public function test_project_report_csv_download(): void
    {
        ['owner' => $owner, 'project' => $project] = $this->createProjectWithTasks();

        $response = $this->actingAs($owner)
            ->get(route('reports.projects.show', ['project' => $project->id, 'format' => 'csv']));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $this->assertStringContainsString('Finished task', $response->streamedContent());
    }

    public function test_user_task_report_pdf_download(): void
    {
        ['member' => $member, 'project' => $project] = $this->createProjectWithTasks();

        $response = $this->actingAs($member)
            ->get(route('reports.user-tasks', ['project' => $project->id, 'format' => 'pdf']));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }

    public function test_project_report_pdf_download(): void
    {
        ['owner' => $owner, 'project' => $project] = $this->createProjectWithTasks();

        $response = $this->actingAs($owner)
            ->get(route('reports.projects.show', ['project' => $project->id, 'format' => 'pdf']));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }
}

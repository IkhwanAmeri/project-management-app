<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\User;
use App\Services\AttachmentService;
use App\Services\CommentService;
use App\Services\ProjectService;
use App\Services\TaskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ActivityLoggingTest extends TestCase
{
    use RefreshDatabase;

    public function test_services_record_project_task_comment_file_status_and_deletion_activities(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $project = app(ProjectService::class)->create([
            'name' => 'Activity Project',
            'status' => 'planning',
        ], $user);

        $task = app(TaskService::class)->create($project, [
            'title' => 'Activity Task',
            'status' => 'Todo',
            'priority' => 'Medium',
        ], $user);

        app(CommentService::class)->create($task, $user, 'Activity comment');
        app(AttachmentService::class)->create($task, $user, UploadedFile::fake()->create('notes.pdf', 20));
        app(TaskService::class)->update($task, ['status' => 'Review'], $user);
        app(TaskService::class)->delete($task, $user);

        $this->assertDatabaseHas('activities', ['action' => 'project_created', 'description' => 'Created project']);
        $this->assertDatabaseHas('activities', ['action' => 'task_created', 'description' => 'Created task']);
        $this->assertDatabaseHas('activities', ['action' => 'comment_added', 'description' => 'Added comment']);
        $this->assertDatabaseHas('activities', ['action' => 'file_uploaded', 'description' => 'Uploaded file']);
        $this->assertDatabaseHas('activities', ['action' => 'task_status_changed', 'description' => 'Todo → Review']);
        $this->assertDatabaseHas('activities', ['action' => 'task_deleted', 'description' => 'Deleted task']);
    }

    public function test_priority_change_logs_activity(): void
    {
        $user = User::factory()->create();
        $project = app(ProjectService::class)->create([
            'name' => 'Priority Project',
            'status' => 'active',
        ], $user);
        $task = app(TaskService::class)->create($project, [
            'title' => 'Priority Task',
            'status' => 'Todo',
            'priority' => 'Low',
        ], $user);

        app(TaskService::class)->update($task, ['priority' => 'High'], $user);

        $this->assertDatabaseHas('activities', [
            'action' => 'task_priority_changed',
            'description' => 'Low → High',
        ]);

        $activity = Activity::where('action', 'task_priority_changed')->latest()->first();
        $this->assertEquals('Low', $activity->properties['from']);
        $this->assertEquals('High', $activity->properties['to']);
    }

    public function test_assignee_change_logs_activity(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $project = app(ProjectService::class)->create([
            'name' => 'Assignee Project',
            'status' => 'active',
        ], $owner);
        $project->members()->attach($member->id, ['role' => 'Member', 'joined_at' => now()]);
        $task = app(TaskService::class)->create($project, [
            'title' => 'Assignee Task',
            'status' => 'Todo',
            'priority' => 'Medium',
        ], $owner);

        app(TaskService::class)->update($task, ['assigned_to' => $member->id], $owner);

        $this->assertDatabaseHas('activities', [
            'action' => 'task_assignee_changed',
        ]);

        $activity = Activity::where('action', 'task_assignee_changed')->latest()->first();
        $this->assertEquals($member->id, $activity->properties['to']);
        $this->assertStringContainsString($member->name, $activity->description);
    }

    public function test_task_duplication_logs_activity(): void
    {
        $user = User::factory()->create();
        $project = app(ProjectService::class)->create([
            'name' => 'Dup Project',
            'status' => 'active',
        ], $user);
        $task = app(TaskService::class)->create($project, [
            'title' => 'Original Task',
            'status' => 'Todo',
            'priority' => 'Medium',
        ], $user);

        $duplicate = app(TaskService::class)->duplicate($task, $user);

        $this->assertDatabaseHas('activities', [
            'action' => 'task_duplicated',
        ]);

        $activity = Activity::where('action', 'task_duplicated')->latest()->first();
        $this->assertEquals($task->id, $activity->properties['original_task_id']);
    }

    public function test_subtask_creation_logs_activity(): void
    {
        $user = User::factory()->create();
        $project = app(ProjectService::class)->create([
            'name' => 'Subtask Project',
            'status' => 'active',
        ], $user);
        $parent = app(TaskService::class)->create($project, [
            'title' => 'Parent Task',
            'status' => 'Todo',
            'priority' => 'Medium',
        ], $user);

        $subtask = app(TaskService::class)->createSubtask($parent, [
            'title' => 'Child Task',
            'status' => 'Todo',
            'priority' => 'Low',
        ], $user);

        $this->assertDatabaseHas('activities', ['action' => 'task_created']);
        $this->assertDatabaseHas('activities', ['action' => 'subtask_created']);

        $activity = Activity::where('action', 'subtask_created')->latest()->first();
        $this->assertEquals($parent->id, $activity->properties['parent_task_id']);
    }

    public function test_status_change_properties_contain_old_and_new(): void
    {
        $user = User::factory()->create();
        $project = app(ProjectService::class)->create([
            'name' => 'Props Project',
            'status' => 'active',
        ], $user);
        $task = app(TaskService::class)->create($project, [
            'title' => 'Props Task',
            'status' => 'Todo',
            'priority' => 'Medium',
        ], $user);

        app(TaskService::class)->update($task, ['status' => 'Completed'], $user);

        $activity = Activity::where('action', 'task_status_changed')->latest()->first();
        $this->assertEquals('Todo', $activity->properties['from']);
        $this->assertEquals('Completed', $activity->properties['to']);
    }

    public function test_activity_index_page_renders(): void
    {
        $user = User::factory()->create();
        $project = app(ProjectService::class)->create([
            'name' => 'Index Project',
            'status' => 'active',
        ], $user);

        $response = $this->actingAs($user)->get(route('activities.index'));
        $response->assertOk();
        $response->assertSee('Activity Log');
    }

    public function test_activity_show_page_renders(): void
    {
        $user = User::factory()->create();
        $project = app(ProjectService::class)->create([
            'name' => 'Show Project',
            'status' => 'active',
        ], $user);
        $task = app(TaskService::class)->create($project, [
            'title' => 'Show Task',
            'status' => 'Todo',
            'priority' => 'Medium',
        ], $user);

        $activity = Activity::where('action', 'task_created')->latest()->first();

        $response = $this->actingAs($user)->get(route('activities.show', $activity));
        $response->assertOk();
        $response->assertSee('Activity Details');
        $response->assertSee('Created task');
    }

    public function test_unauthenticated_user_cannot_view_activities(): void
    {
        $response = $this->get(route('activities.index'));
        $response->assertRedirect();
    }
}

<?php

namespace Tests\Feature;

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
}

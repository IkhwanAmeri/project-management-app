<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TaskCommentAndAttachmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_a_comment_and_upload_an_attachment_to_a_task(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $project = $user->ownedProjects()->create([
            'name' => 'Task Files',
            'slug' => 'task-files',
            'status' => 'active',
        ]);
        $project->members()->attach($user->id, ['role' => 'Owner', 'joined_at' => now()]);
        $task = $project->tasks()->create([
            'title' => 'Upload test',
            'priority' => 'Medium',
            'status' => 'Todo',
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->post(route('tasks.comments.store', $task), ['comment' => 'Looks good.'])
            ->assertRedirect(route('tasks.show', $task));

        $this->actingAs($user)
            ->post(route('tasks.attachments.store', $task), [
                'attachment' => UploadedFile::fake()->create('specification.pdf', 100),
            ])
            ->assertRedirect(route('tasks.show', $task));

        $this->assertDatabaseHas('task_comments', [
            'task_id' => $task->id,
            'user_id' => $user->id,
            'comment' => 'Looks good.',
        ]);
        $this->assertDatabaseHas('activities', ['action' => 'comment_added', 'task_id' => $task->id]);
        $this->assertDatabaseHas('activities', ['action' => 'file_uploaded', 'task_id' => $task->id]);

        $attachment = $task->attachments()->firstOrFail();
        Storage::disk('public')->assertExists($attachment->file_path);
    }
}

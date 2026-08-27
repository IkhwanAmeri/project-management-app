<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubtaskTest extends TestCase
{
    use RefreshDatabase;

    private function createProjectWithTask(): array
    {
        $owner = User::factory()->create();
        $project = $owner->ownedProjects()->create([
            'name' => 'Subtask Test Project',
            'slug' => 'subtask-test-project',
            'status' => 'active',
        ]);
        $project->members()->attach($owner->id, ['role' => 'Owner', 'joined_at' => now()]);
        $task = $project->tasks()->create([
            'title' => 'Parent Task',
            'priority' => 'Medium',
            'status' => 'Todo',
            'created_by' => $owner->id,
        ]);

        return compact('owner', 'project', 'task');
    }

    public function test_member_can_create_a_subtask(): void
    {
        ['owner' => $owner, 'task' => $task] = $this->createProjectWithTask();

        $response = $this->actingAs($owner)
            ->post(route('tasks.subtasks.store', $task), [
                'title' => 'My Subtask',
                'priority' => 'Low',
                'status' => 'Todo',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tasks', [
            'title' => 'My Subtask',
            'parent_task_id' => $task->id,
            'project_id' => $task->project_id,
        ]);
    }

    public function test_task_cannot_be_its_own_parent(): void
    {
        ['owner' => $owner, 'task' => $task] = $this->createProjectWithTask();

        $response = $this->actingAs($owner)
            ->post(route('tasks.subtasks.store', $task), [
                'title' => 'Self Reference',
                'priority' => 'Low',
                'status' => 'Todo',
                'parent_task_id' => $task->id,
            ]);

        $response->assertSessionHasErrors('parent_task_id');
    }

    public function test_cannot_create_subtask_under_another_project_task(): void
    {
        ['owner' => $owner, 'task' => $task] = $this->createProjectWithTask();

        $otherProject = $owner->ownedProjects()->create([
            'name' => 'Other Project',
            'slug' => 'other-project',
            'status' => 'active',
        ]);
        $otherProject->members()->attach($owner->id, ['role' => 'Owner', 'joined_at' => now()]);
        $otherTask = $otherProject->tasks()->create([
            'title' => 'Other Task',
            'priority' => 'Medium',
            'status' => 'Todo',
            'created_by' => $owner->id,
        ]);

        $response = $this->actingAs($owner)
            ->post(route('tasks.subtasks.store', $task), [
                'title' => 'Cross Project',
                'priority' => 'Low',
                'status' => 'Todo',
                'parent_task_id' => $otherTask->id,
            ]);

        $response->assertSessionHasErrors('parent_task_id');
    }

    public function test_cannot_nest_deeper_than_one_level(): void
    {
        ['owner' => $owner, 'task' => $task] = $this->createProjectWithTask();

        $subtask = $task->subtasks()->create([
            'title' => 'Level 1 Subtask',
            'priority' => 'Low',
            'status' => 'Todo',
            'project_id' => $task->project_id,
            'created_by' => $owner->id,
        ]);

        $response = $this->actingAs($owner)
            ->post(route('tasks.subtasks.store', $subtask), [
                'title' => 'Level 2 Subtask',
                'priority' => 'Low',
                'status' => 'Todo',
            ]);

        $response->assertSessionHasErrors('parent_task_id');
    }

    public function test_subtask_progress_calculation(): void
    {
        ['owner' => $owner, 'task' => $task] = $this->createProjectWithTask();

        $task->subtasks()->create([
            'title' => 'Done',
            'priority' => 'Low',
            'status' => 'Completed',
            'project_id' => $task->project_id,
            'created_by' => $owner->id,
        ]);
        $task->subtasks()->create([
            'title' => 'Not Done',
            'priority' => 'Low',
            'status' => 'Todo',
            'project_id' => $task->project_id,
            'created_by' => $owner->id,
        ]);
        $task->subtasks()->create([
            'title' => 'Also Done',
            'priority' => 'Low',
            'status' => 'Completed',
            'project_id' => $task->project_id,
            'created_by' => $owner->id,
        ]);

        $task->load('subtasks');

        $this->assertEquals(2, $task->completedSubtasksCount());
        $this->assertEquals(67, $task->subtaskProgress());
    }

    public function test_progress_is_zero_when_no_subtasks(): void
    {
        ['task' => $task] = $this->createProjectWithTask();

        $task->load('subtasks');

        $this->assertEquals(0, $task->completedSubtasksCount());
        $this->assertEquals(0, $task->subtaskProgress());
    }

    public function test_non_member_cannot_create_subtask(): void
    {
        ['task' => $task] = $this->createProjectWithTask();
        $nonMember = User::factory()->create();

        $response = $this->actingAs($nonMember)
            ->post(route('tasks.subtasks.store', $task), [
                'title' => 'Unauthorized',
                'priority' => 'Low',
                'status' => 'Todo',
            ]);

        $response->assertForbidden();
    }

    public function test_subtask_belongs_to_same_project_as_parent(): void
    {
        ['owner' => $owner, 'task' => $task] = $this->createProjectWithTask();

        $this->actingAs($owner)
            ->post(route('tasks.subtasks.store', $task), [
                'title' => 'Same Project Subtask',
                'priority' => 'High',
                'status' => 'In Progress',
            ]);

        $subtask = Task::where('title', 'Same Project Subtask')->first();

        $this->assertEquals($task->project_id, $subtask->project_id);
    }

    public function test_show_task_displays_subtasks(): void
    {
        ['owner' => $owner, 'task' => $task] = $this->createProjectWithTask();

        $task->subtasks()->create([
            'title' => 'Visible Subtask',
            'priority' => 'Low',
            'status' => 'Todo',
            'project_id' => $task->project_id,
            'created_by' => $owner->id,
        ]);

        $response = $this->actingAs($owner)
            ->get(route('tasks.show', $task));

        $response->assertOk();
        $response->assertSee('Visible Subtask');
        $response->assertSee('0 / 1 completed');
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\TaskDueSoonNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskDueSoonCommandTest extends TestCase
{
    use RefreshDatabase;

    private function createOpenTask(string|int|\DateTimeInterface $dueDate, array $attributes = []): array
    {
        $owner = User::factory()->create();
        $assignee = User::factory()->create();

        $project = $owner->ownedProjects()->create([
            'name' => 'Due Soon Project',
            'slug' => 'due-soon-project-'.uniqid(),
            'status' => 'active',
        ]);
        $project->members()->attach($owner->id, ['role' => 'Owner', 'joined_at' => now()]);
        $project->members()->attach($assignee->id, ['role' => 'Member', 'joined_at' => now()]);

        $task = $project->tasks()->create([
            'title' => 'Deadline task',
            'priority' => 'High',
            'status' => 'Todo',
            'created_by' => $owner->id,
            'assigned_to' => $assignee->id,
            'due_date' => $dueDate,
            ...$attributes,
        ]);

        return compact('owner', 'assignee', 'project', 'task');
    }

    public function test_notifies_assignee_about_task_due_tomorrow(): void
    {
        ['assignee' => $assignee, 'task' => $task] = $this->createOpenTask(now()->addDay());

        $this->artisan('tasks:notify-due-soon')->assertExitCode(0);

        $this->assertTrue(
            $assignee->notifications()
                ->where('type', TaskDueSoonNotification::class)
                ->where('data->task_id', $task->id)
                ->exists()
        );
    }

    public function test_does_not_notify_tasks_due_later_or_today(): void
    {
        $this->createOpenTask(now()->addDays(2));
        $this->createOpenTask(now());

        $this->artisan('tasks:notify-due-soon')->assertExitCode(0);

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_skips_completed_and_cancelled_tasks(): void
    {
        $this->createOpenTask(now()->addDay(), ['status' => 'Completed']);
        $this->createOpenTask(now()->addDay(), ['status' => 'Cancelled']);

        $this->artisan('tasks:notify-due-soon')->assertExitCode(0);

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_skips_unassigned_tasks(): void
    {
        $this->createOpenTask(now()->addDay(), ['assigned_to' => null]);

        $this->artisan('tasks:notify-due-soon')->assertExitCode(0);

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_does_not_duplicate_on_second_run(): void
    {
        ['assignee' => $assignee, 'task' => $task] = $this->createOpenTask(now()->addDay());

        $this->artisan('tasks:notify-due-soon')->assertExitCode(0);
        $this->artisan('tasks:notify-due-soon')->assertExitCode(0);

        $count = $assignee->notifications()
            ->where('type', TaskDueSoonNotification::class)
            ->where('data->task_id', $task->id)
            ->count();

        $this->assertSame(1, $count);
    }
}

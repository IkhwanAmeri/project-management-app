<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $projectIds = $user->projects()->pluck('projects.id');

        $tasks = Task::query()
            ->with('project')
            ->whereIn('project_id', $projectIds)
            ->where(fn ($q) => $q->whereNotNull('start_date')->orWhereNotNull('due_date'))
            ->get()
            ->map(fn (Task $task) => [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status,
                'priority' => $task->priority,
                'start_date' => $task->start_date?->toDateString(),
                'due_date' => $task->due_date?->toDateString(),
                'project' => $task->project->name,
                'url' => route('tasks.show', $task),
            ]);

        return view('calendar', ['tasks' => $tasks]);
    }
}

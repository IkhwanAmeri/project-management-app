<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    /**
     * Display project and task statistics for the authenticated user.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $projectIds = $user->projects()->pluck('projects.id');
        $projectTasks = Task::query()->whereIn('project_id', $projectIds);

        return view('dashboard', [
            'statistics' => [
                ['Projects', $projectIds->count()],
                ['Open Tasks', (clone $projectTasks)->pending()->count()],
                ['Completed Today', (clone $projectTasks)->where('status', 'Completed')->whereDate('completed_at', Carbon::today())->count()],
                ['Overdue', (clone $projectTasks)->overdue()->count()],
            ],
            'tasks' => $user->assignedTasks()
                ->with('project')
                ->pending()
                ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
                ->orderBy('due_date')
                ->limit(5)
                ->get(),
            'recentComments' => TaskComment::query()
                ->with(['user', 'task.project'])
                ->whereHas('task', fn ($query) => $query->whereIn('project_id', $projectIds))
                ->where('user_id', '!=', $user->id)
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}

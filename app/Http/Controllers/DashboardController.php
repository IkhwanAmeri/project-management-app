<?php

namespace App\Http\Controllers;

use App\Models\Task;
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
        $openStatuses = ['Todo', 'In Progress', 'Review'];

        $projectTasks = Task::query()->whereIn('project_id', $projectIds);

        return view('dashboard', [
            'statistics' => [
                ['Projects', $projectIds->count()],
                ['Open Tasks', (clone $projectTasks)->whereIn('status', $openStatuses)->count()],
                ['Completed Today', (clone $projectTasks)->whereDate('completed_at', Carbon::today())->count()],
                ['Overdue', (clone $projectTasks)
                    ->whereIn('status', $openStatuses)
                    ->whereDate('due_date', '<', Carbon::today())
                    ->count()],
            ],
            'tasks' => $user->assignedTasks()
                ->with('project')
                ->whereIn('status', $openStatuses)
                ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
                ->orderBy('due_date')
                ->limit(5)
                ->get(),
        ]);
    }
}

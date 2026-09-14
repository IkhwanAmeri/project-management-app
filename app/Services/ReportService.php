<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class ReportService
{
    /** @var list<string> */
    private const TASK_STATUSES = ['Todo', 'In Progress', 'Review', 'Completed', 'Cancelled'];

    /** @var list<string> */
    private const OPEN_STATUSES = ['Todo', 'In Progress', 'Review'];

    /** @var list<string> */
    private const TASK_PRIORITIES = ['Low', 'Medium', 'High', 'Critical'];

    /**
     * Aggregate task statistics for a user's assignments, optionally scoped to one project.
     *
     * @return array<string, mixed>
     */
    public function userTaskReport(User $user, ?Project $project = null): array
    {
        $base = fn () => Task::query()
            ->where('assigned_to', $user->id)
            ->when($project, fn ($query) => $query->where('project_id', $project->id));

        $byStatus = $base()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $byPriority = $base()
            ->selectRaw('priority, COUNT(*) as total')
            ->groupBy('priority')
            ->pluck('total', 'priority')
            ->all();

        $hours = $base()
            ->selectRaw(
                'COALESCE(SUM(estimated_hours), 0) as estimated, COALESCE(SUM(actual_hours), 0) as actual, COALESCE(SUM(CASE WHEN status = ? THEN actual_hours ELSE 0 END), 0) as completed_actual',
                ['Completed']
            )
            ->first();

        $tasks = $base()
            ->with('project:id,name')
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_date')
            ->get(['id', 'project_id', 'title', 'status', 'priority', 'due_date', 'completed_at']);

        $completed = $byStatus['Completed'] ?? 0;
        $total = $tasks->count();

        return [
            'user' => $user,
            'project' => $project,
            'total_tasks' => $total,
            'status_list' => self::TASK_STATUSES,
            'status_counts' => $byStatus,
            'priority_list' => self::TASK_PRIORITIES,
            'priority_counts' => $byPriority,
            'open_count' => array_sum(array_intersect_key($byStatus, array_flip(self::OPEN_STATUSES))),
            'completed_count' => $completed,
            'cancelled_count' => $byStatus['Cancelled'] ?? 0,
            'overdue_count' => $base()->overdue()->count(),
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100) : 0,
            'estimated_hours' => round((float) $hours->estimated, 2),
            'actual_hours' => round((float) $hours->actual, 2),
            'completed_hours' => round((float) $hours->completed_actual, 2),
            'tasks' => $tasks,
        ];
    }

    /**
     * Aggregate project statistics including member workload.
     *
     * @return array<string, mixed>
     */
    public function projectReport(Project $project): array
    {
        $base = fn () => $project->tasks();

        $byStatus = (clone $base())
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        $byPriority = (clone $base())
            ->selectRaw('priority, COUNT(*) as total')
            ->groupBy('priority')
            ->pluck('total', 'priority')
            ->all();

        $hours = (clone $base())
            ->selectRaw('COALESCE(SUM(estimated_hours), 0) as estimated, COALESCE(SUM(actual_hours), 0) as actual')
            ->first();

        $tasks = (clone $base())
            ->with('assignedUser:id,name')
            ->orderByRaw('CASE WHEN status = ? THEN 1 ELSE 0 END', ['Completed'])
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_date')
            ->get(['id', 'title', 'status', 'priority', 'assigned_to', 'due_date', 'completed_at', 'estimated_hours', 'actual_hours']);

        $workload = $project->tasks()
            ->whereNotNull('assigned_to')
            ->selectRaw(
                'assigned_to, COUNT(*) as assigned_total, SUM(CASE WHEN status IN (?, ?, ?) THEN 1 ELSE 0 END) as open_total, COALESCE(SUM(estimated_hours), 0) as estimated_hours',
                self::OPEN_STATUSES
            )
            ->groupBy('assigned_to')
            ->get()
            ->keyBy('assigned_to');

        $members = $project->members()
            ->select('users.id', 'users.name', 'project_members.role')
            ->orderBy('users.name')
            ->get()
            ->map(fn (User $member): array => [
                'id' => $member->id,
                'name' => $member->name,
                'role' => $member->pivot->role,
                'assigned_total' => (int) ($workload[$member->id]->assigned_total ?? 0),
                'open_total' => (int) ($workload[$member->id]->open_total ?? 0),
                'estimated_hours' => round((float) ($workload[$member->id]->estimated_hours ?? 0), 2),
            ])
            ->values()
            ->all();

        $unassigned = $project->tasks()
            ->whereNull('assigned_to')
            ->selectRaw('COUNT(*) as total, COALESCE(SUM(estimated_hours), 0) as estimated_hours')
            ->first();

        $completed = $byStatus['Completed'] ?? 0;
        $total = $tasks->count();

        return [
            'project' => $project,
            'total_tasks' => $total,
            'status_list' => self::TASK_STATUSES,
            'status_counts' => $byStatus,
            'priority_list' => self::TASK_PRIORITIES,
            'priority_counts' => $byPriority,
            'open_count' => array_sum(array_intersect_key($byStatus, array_flip(self::OPEN_STATUSES))),
            'completed_count' => $completed,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100) : 0,
            'estimated_hours' => round((float) $hours->estimated, 2),
            'actual_hours' => round((float) $hours->actual, 2),
            'unassigned_total' => (int) $unassigned->total,
            'unassigned_hours' => round((float) $unassigned->estimated_hours, 2),
            'members' => $members,
            'tasks' => $tasks,
        ];
    }
}

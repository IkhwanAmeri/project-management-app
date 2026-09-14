<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reportService) {}

    /**
     * Display the report hub.
     */
    public function index(Request $request): View
    {
        $projects = $request->user()->projects()
            ->orderBy('projects.name')
            ->get(['projects.id', 'projects.name']);

        return view('reports.index', compact('projects'));
    }

    /**
     * Display, export, or print a user task report.
     */
    public function userTasks(Request $request): View|Response|StreamedResponse
    {
        $viewer = $request->user();
        $validated = $request->validate([
            'user' => ['nullable', 'integer'],
            'project' => ['nullable', 'integer'],
        ]);

        $project = null;

        if (isset($validated['project'])) {
            $project = $viewer->projects()
                ->where('projects.id', $validated['project'])
                ->firstOrFail();
        }

        $target = $this->resolveTargetUser($viewer, (int) ($validated['user'] ?? $viewer->id), $project);

        $report = $this->reportService->userTaskReport($target, $project);

        $format = strtolower($request->query('format', 'html'));

        return match ($format) {
            'csv' => $this->userTasksCsv($report),
            'pdf' => $this->userTasksPdf($report),
            default => view('reports.user-tasks', [
                ...$report,
                'projects' => $viewer->projects()
                    ->orderBy('projects.name')
                    ->get(['projects.id', 'projects.name']),
                'canSelectMembers' => $project !== null && $viewer->hasProjectRole($project, 'Owner', 'Manager'),
                'members' => $project?->members()
                    ->orderBy('users.name')
                    ->get(['users.id', 'users.name']),
            ]),
        };
    }

    /**
     * Display, export, or print a project report.
     */
    public function project(Request $request, Project $project): View|Response|StreamedResponse
    {
        $this->authorize('view', $project);

        $report = $this->reportService->projectReport($project);

        $format = strtolower($request->query('format', 'html'));

        return match ($format) {
            'csv' => $this->projectCsv($report),
            'pdf' => $this->projectPdf($report),
            default => view('reports.project', $report),
        };
    }

    /**
     * Resolve the report target user, enforcing that managers/owners may only
     * inspect other users' reports within projects where they hold that role.
     */
    private function resolveTargetUser(User $viewer, int $userId, ?Project $project): User
    {
        if ($userId === $viewer->id) {
            return $viewer;
        }

        if ($project === null) {
            abort(Response::HTTP_FORBIDDEN);
        }

        if (! $viewer->hasProjectRole($project, 'Owner', 'Manager')) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $project->members()
            ->where('users.id', $userId)
            ->firstOrFail();
    }

    private function userTasksCsv(array $report): StreamedResponse
    {
        return response()->streamDownload(function () use ($report): void {
            $output = fopen('php://output', 'w');

            fputcsv($output, ['User Task Report']);
            fputcsv($output, ['User', $report['user']->name]);
            fputcsv($output, ['Project', $report['project']?->name ?? 'All projects']);
            fputcsv($output, ['Generated', now()->toDateTimeString()]);
            fputcsv($output, ['Total Tasks', $report['total_tasks']]);
            fputcsv($output, ['Open', $report['open_count']]);
            fputcsv($output, ['Completed', $report['completed_count']]);
            fputcsv($output, ['Cancelled', $report['cancelled_count']]);
            fputcsv($output, ['Overdue', $report['overdue_count']]);
            fputcsv($output, ['Completion Rate (%)', $report['completion_rate']]);
            fputcsv($output, ['Estimated Hours', $report['estimated_hours']]);
            fputcsv($output, ['Actual Hours', $report['actual_hours']]);
            fputcsv($output, ['Hours Logged (Completed)', $report['completed_hours']]);
            fputcsv($output, []);

            fputcsv($output, ['Task', 'Project', 'Status', 'Priority', 'Due Date', 'Completed At']);
            foreach ($report['tasks'] as $task) {
                fputcsv($output, [
                    $task->title,
                    $task->project->name,
                    $task->status,
                    $task->priority,
                    $task->due_date?->toDateString() ?? '',
                    $task->completed_at?->toDateString() ?? '',
                ]);
            }

            fclose($output);
        }, 'user-task-report-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    private function projectCsv(array $report): StreamedResponse
    {
        return response()->streamDownload(function () use ($report): void {
            $output = fopen('php://output', 'w');

            fputcsv($output, ['Project Report']);
            fputcsv($output, ['Project', $report['project']->name]);
            fputcsv($output, ['Generated', now()->toDateTimeString()]);
            fputcsv($output, ['Total Tasks', $report['total_tasks']]);
            fputcsv($output, ['Open', $report['open_count']]);
            fputcsv($output, ['Completed', $report['completed_count']]);
            fputcsv($output, ['Completion Rate (%)', $report['completion_rate']]);
            fputcsv($output, ['Estimated Hours', $report['estimated_hours']]);
            fputcsv($output, ['Actual Hours', $report['actual_hours']]);
            fputcsv($output, ['Unassigned Tasks', $report['unassigned_total']]);
            fputcsv($output, []);
            fputcsv($output, ['Member Workload']);

            fputcsv($output, ['Member', 'Role', 'Assigned Tasks', 'Open', 'Estimated Hours']);
            foreach ($report['members'] as $member) {
                fputcsv($output, [$member['name'], $member['role'], $member['assigned_total'], $member['open_total'], $member['estimated_hours']]);
            }
            fputcsv($output, []);

            fputcsv($output, ['Task', 'Status', 'Priority', 'Assignee', 'Due Date', 'Completed At']);
            foreach ($report['tasks'] as $task) {
                fputcsv($output, [
                    $task->title,
                    $task->status,
                    $task->priority,
                    $task->assignedUser?->name ?? 'Unassigned',
                    $task->due_date?->toDateString() ?? '',
                    $task->completed_at?->toDateString() ?? '',
                ]);
            }

            fclose($output);
        }, "project-report-{$report['project']->slug}-".now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv']);
    }

    private function userTasksPdf(array $report): Response
    {
        $pdf = Pdf::loadView('reports.pdf.user', [
            ...$report,
            'generatedAt' => now(),
        ]);

        return $pdf->download('user-task-report-'.now()->format('Y-m-d').'.pdf');
    }

    private function projectPdf(array $report): Response
    {
        $pdf = Pdf::loadView('reports.pdf.project', [
            ...$report,
            'generatedAt' => now(),
        ]);

        return $pdf->download("project-report-{$report['project']->slug}-".now()->format('Y-m-d').'.pdf');
    }
}

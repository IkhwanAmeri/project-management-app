<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $project->name }} — Project Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; }
        h1 { font-size: 18px; margin: 0 0 4px; color: #111827; }
        h2 { font-size: 12px; margin: 18px 0 6px; color: #111827; }
        .meta { color: #6b7280; font-size: 9px; margin-bottom: 14px; }
        .summary { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        .summary td { padding: 3px 8px; border: 1px solid #e5e7eb; }
        .summary td.key { background: #f3f4f6; font-weight: bold; width: 55%; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.data th { background: #f3f4f6; text-align: left; padding: 5px 8px; border: 1px solid #e5e7eb; font-size: 9px; text-transform: uppercase; }
        table.data td { padding: 4px 8px; border: 1px solid #e5e7eb; }
    </style>
</head>
<body>
    <h1>Project Report</h1>
    <div class="meta">
        {{ $project->name }} &middot; Status: {{ $project->status }} &middot; Generated {{ $generatedAt->toDateTimeString() }}
    </div>

    <h2>Summary</h2>
    <table class="summary">
        <tr><td class="key">Total tasks</td><td>{{ $total_tasks }}</td></tr>
        <tr><td class="key">Open tasks</td><td>{{ $open_count }}</td></tr>
        <tr><td class="key">Completed tasks</td><td>{{ $completed_count }}</td></tr>
        <tr><td class="key">Completion rate</td><td>{{ $completion_rate }}%</td></tr>
        <tr><td class="key">Estimated hours</td><td>{{ number_format($estimated_hours, 1) }}h</td></tr>
        <tr><td class="key">Actual hours</td><td>{{ number_format($actual_hours, 1) }}h</td></tr>
        <tr><td class="key">Unassigned tasks</td><td>{{ $unassigned_total }} ({{ number_format($unassigned_hours, 1) }}h estimated)</td></tr>
    </table>

    <h2>Member Workload</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Member</th>
                <th>Role</th>
                <th>Assigned</th>
                <th>Open</th>
                <th>Estimated hours</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($members as $member)
                <tr>
                    <td>{{ $member['name'] }}</td>
                    <td>{{ $member['role'] }}</td>
                    <td>{{ $member['assigned_total'] }}</td>
                    <td>{{ $member['open_total'] }}</td>
                    <td>{{ number_format($member['estimated_hours'], 1) }}h</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Tasks</h2>
    @if ($tasks->isEmpty())
        <p>No tasks in this project.</p>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th>Task</th>
                    <th>Status</th>
                    <th>Priority</th>
                    <th>Assignee</th>
                    <th>Due date</th>
                    <th>Completed</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tasks as $task)
                    <tr>
                        <td>{{ $task->title }}</td>
                        <td>{{ $task->status }}</td>
                        <td>{{ $task->priority }}</td>
                        <td>{{ $task->assignedUser?->name ?? 'Unassigned' }}</td>
                        <td>{{ $task->due_date?->toDateString() ?? '-' }}</td>
                        <td>{{ $task->completed_at?->toDateString() ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('Project Report') }}: {{ $project->name }}
            </h2>
            <a href="{{ route('reports.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">
                &larr; All reports
            </a>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-indigo-100 via-purple-50 to-pink-50 py-12">
        <div class="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">

            {{-- Print / download --}}
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-gray-600">
                    Status: <span class="font-semibold text-gray-900">{{ $project->status }}</span>
                    &middot; {{ $project->start_date?->toDateString() ?? 'no start date' }} &rarr; {{ $project->end_date?->toDateString() ?? 'no end date' }}
                </p>
                <div class="flex gap-2">
                    <a href="{{ route('reports.projects.show', ['project' => $project->id, 'format' => 'csv']) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-white/40 bg-white/60 px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-white">
                        Download CSV
                    </a>
                    <a href="{{ route('reports.projects.show', ['project' => $project->id, 'format' => 'pdf']) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-white/40 bg-white/60 px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-white">
                        Download PDF
                    </a>
                </div>
            </div>

            {{-- Stat Cards --}}
            @php
                $statCards = [
                    ['label' => 'Total Tasks', 'value' => $total_tasks, 'styles' => 'border-l-indigo-500 bg-indigo-500/10 text-indigo-600'],
                    ['label' => 'Open', 'value' => $open_count, 'styles' => 'border-l-blue-500 bg-blue-500/10 text-blue-600'],
                    ['label' => 'Completed', 'value' => $completed_count, 'styles' => 'border-l-emerald-500 bg-emerald-500/10 text-emerald-600'],
                    ['label' => 'Completion Rate', 'value' => $completion_rate.'%', 'styles' => 'border-l-purple-500 bg-purple-500/10 text-purple-600'],
                    ['label' => 'Estimated Hours', 'value' => number_format($estimated_hours, 1).'h', 'styles' => 'border-l-amber-500 bg-amber-500/10 text-amber-600'],
                    ['label' => 'Actual Hours', 'value' => number_format($actual_hours, 1).'h', 'styles' => 'border-l-rose-500 bg-rose-500/10 text-rose-600'],
                ];
            @endphp
            <div class="grid gap-5 sm:grid-cols-3 lg:grid-cols-6">
                @foreach ($statCards as $card)
                    <div class="rounded-2xl border border-white/40 border-l-4 {{ $card['styles'] }} bg-white/60 p-5 shadow-lg backdrop-blur-sm">
                        <p class="text-sm font-medium text-gray-500">{{ $card['label'] }}</p>
                        <p class="mt-2 text-2xl font-bold tracking-tight text-gray-900">{{ $card['value'] }}</p>
                    </div>
                @endforeach
            </div>

            {{-- Breakdowns --}}
            <div class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-white/40 bg-white/60 p-6 shadow-lg backdrop-blur-sm">
                    <h3 class="mb-4 text-base font-semibold text-gray-900">By Status</h3>
                    @foreach ($status_list as $status)
                        @php
                            $count = $status_counts[$status] ?? 0;
                            $pct = $total_tasks > 0 ? round(($count / $total_tasks) * 100) : 0;
                        @endphp
                        <div class="mb-3">
                            <div class="mb-1 flex items-center justify-between text-xs">
                                <span class="font-medium text-gray-700">{{ $status }}</span>
                                <span class="text-gray-500">{{ $count }} ({{ $pct }}%)</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-indigo-400" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="rounded-2xl border border-white/40 bg-white/60 p-6 shadow-lg backdrop-blur-sm">
                    <h3 class="mb-4 text-base font-semibold text-gray-900">By Priority</h3>
                    @foreach ($priority_list as $priority)
                        @php
                            $count = $priority_counts[$priority] ?? 0;
                            $pct = $total_tasks > 0 ? round(($count / $total_tasks) * 100) : 0;
                        @endphp
                        <div class="mb-3">
                            <div class="mb-1 flex items-center justify-between text-xs">
                                <span class="font-medium text-gray-700">{{ $priority }}</span>
                                <span class="text-gray-500">{{ $count }} ({{ $pct }}%)</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-amber-400" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                    <div class="mt-5 rounded-xl bg-gray-50 px-4 py-3 text-sm text-gray-600">
                        <span class="font-semibold text-gray-900">{{ $unassigned_total }}</span> tasks are unassigned
                        ({{ number_format($unassigned_hours, 1) }}h estimated).
                    </div>
                </div>
            </div>

            {{-- Member workload --}}
            <div class="overflow-hidden rounded-2xl border border-white/40 bg-white/60 shadow-lg backdrop-blur-sm">
                <div class="border-b border-white/40 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Member Workload</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead>
                            <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <th class="px-6 py-3">Member</th>
                                <th class="px-6 py-3">Role</th>
                                <th class="px-6 py-3">Assigned</th>
                                <th class="px-6 py-3">Open</th>
                                <th class="px-6 py-3">Estimated hours</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($members as $member)
                                <tr class="transition hover:bg-white/70">
                                    <td class="px-6 py-3 font-medium text-gray-900">{{ $member['name'] }}</td>
                                    <td class="px-6 py-3 text-gray-600">{{ $member['role'] }}</td>
                                    <td class="px-6 py-3 text-gray-600">{{ $member['assigned_total'] }}</td>
                                    <td class="px-6 py-3 text-gray-600">{{ $member['open_total'] }}</td>
                                    <td class="px-6 py-3 text-gray-600">{{ number_format($member['estimated_hours'], 1) }}h</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Task table --}}
            <div class="overflow-hidden rounded-2xl border border-white/40 bg-white/60 shadow-lg backdrop-blur-sm">
                <div class="border-b border-white/40 px-6 py-4">
                    <h3 class="text-base font-semibold text-gray-900">Tasks</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                        <thead>
                            <tr class="text-left text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <th class="px-6 py-3">Task</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Priority</th>
                                <th class="px-6 py-3">Assignee</th>
                                <th class="px-6 py-3">Due date</th>
                                <th class="px-6 py-3">Completed</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($tasks as $task)
                                <tr class="transition hover:bg-white/70">
                                    <td class="px-6 py-3"><a href="{{ route('tasks.show', $task) }}" class="font-medium text-gray-900 hover:text-indigo-600">{{ $task->title }}</a></td>
                                    <td class="px-6 py-3 text-gray-600">{{ $task->status }}</td>
                                    <td class="px-6 py-3 text-gray-600">{{ $task->priority }}</td>
                                    <td class="px-6 py-3 text-gray-600">{{ $task->assignedUser?->name ?? 'Unassigned' }}</td>
                                    <td class="px-6 py-3 text-gray-600">{{ $task->due_date?->toDateString() ?? '—' }}</td>
                                    <td class="px-6 py-3 text-gray-600">{{ $task->completed_at?->toDateString() ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">No tasks in this project.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
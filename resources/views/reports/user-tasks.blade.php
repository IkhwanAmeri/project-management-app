<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ __('User Task Report') }}
            </h2>
            <a href="{{ route('reports.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500">
                &larr; All reports
            </a>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-indigo-100 via-purple-50 to-pink-50 py-12">
        <div class="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">

            {{-- Filters --}}
            <form method="GET" action="{{ route('reports.user-tasks') }}" class="rounded-2xl border border-white/40 bg-white/60 p-6 shadow-lg backdrop-blur-sm">
                <div class="grid gap-4 sm:grid-cols-3">
                    <div>
                        <label for="project" class="block text-xs font-medium text-gray-600">Project</label>
                        <select name="project" id="project" class="mt-1 w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All my projects</option>
                            @foreach ($projects as $projectOption)
                                <option value="{{ $projectOption->id }}" @selected($project?->id === $projectOption->id)>{{ $projectOption->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="filter-user" class="block text-xs font-medium text-gray-600">User</label>
                        @if ($canSelectMembers)
                            <select name="user" id="filter-user" class="mt-1 w-full rounded-xl border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach ($members as $member)
                                    <option value="{{ $member->id }}" @selected($user->id === $member->id)>{{ $member->name }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="text" disabled value="{{ $user->name }}" class="mt-1 w-full rounded-xl border-gray-200 bg-gray-50 text-sm text-gray-600">
                            <input type="hidden" name="user" value="{{ $user->id }}">
                        @endif
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                            Apply filters
                        </button>
                    </div>
                </div>
            </form>

            {{-- Print / download --}}
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-sm text-gray-600">
                    <span class="font-semibold text-gray-900">{{ $user->name }}</span>
                    &middot; {{ $project?->name ?? 'All projects' }}
                    &middot; {{ $total_tasks }} tasks
                </p>
                <div class="flex gap-2">
                    <a href="{{ route('reports.user-tasks', ['user' => $user->id, 'project' => $project?->id, 'format' => 'csv']) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-white/40 bg-white/60 px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-white">
                        Download CSV
                    </a>
                    <a href="{{ route('reports.user-tasks', ['user' => $user->id, 'project' => $project?->id, 'format' => 'pdf']) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-white/40 bg-white/60 px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-white">
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
                    ['label' => 'Overdue', 'value' => $overdue_count, 'styles' => 'border-l-rose-500 bg-rose-500/10 text-rose-600'],
                    ['label' => 'Completion Rate', 'value' => $completion_rate.'%', 'styles' => 'border-l-purple-500 bg-purple-500/10 text-purple-600'],
                ];
            @endphp
            <div class="grid gap-5 sm:grid-cols-3 lg:grid-cols-5">
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
                    <h3 class="mb-4 text-base font-semibold text-gray-900">By Priority & Hours</h3>
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
                    <div class="mt-5 grid grid-cols-3 gap-3 text-center">
                        <div class="rounded-xl bg-gray-50 px-3 py-3">
                            <p class="text-xs text-gray-500">Estimated</p>
                            <p class="mt-1 text-lg font-bold text-gray-900">{{ number_format($estimated_hours, 1) }}h</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 px-3 py-3">
                            <p class="text-xs text-gray-500">Actual</p>
                            <p class="mt-1 text-lg font-bold text-gray-900">{{ number_format($actual_hours, 1) }}h</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 px-3 py-3">
                            <p class="text-xs text-gray-500">On completed</p>
                            <p class="mt-1 text-lg font-bold text-emerald-600">{{ number_format($completed_hours, 1) }}h</p>
                        </div>
                    </div>
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
                                <th class="px-6 py-3">Project</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Priority</th>
                                <th class="px-6 py-3">Due date</th>
                                <th class="px-6 py-3">Completed</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($tasks as $task)
                                <tr class="transition hover:bg-white/70">
                                    <td class="px-6 py-3"><a href="{{ route('tasks.show', $task) }}" class="font-medium text-gray-900 hover:text-indigo-600">{{ $task->title }}</a></td>
                                    <td class="px-6 py-3 text-gray-600">{{ $task->project->name }}</td>
                                    <td class="px-6 py-3 text-gray-600">{{ $task->status }}</td>
                                    <td class="px-6 py-3 text-gray-600">{{ $task->priority }}</td>
                                    <td class="px-6 py-3 text-gray-600">{{ $task->due_date?->toDateString() ?? '—' }}</td>
                                    <td class="px-6 py-3 text-gray-600">{{ $task->completed_at?->toDateString() ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">No tasks for this report.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
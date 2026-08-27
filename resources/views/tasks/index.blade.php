<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold leading-tight text-gray-800">Tasks</h2></x-slot>

    <div class="min-h-screen bg-gradient-to-br from-indigo-100 via-purple-50 to-pink-50 py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4 text-sm font-medium text-emerald-700 shadow-sm backdrop-blur-sm">{{ session('status') }}</div>
            @endif

            {{-- Filters --}}
            <form method="GET" class="mb-6 rounded-2xl border border-white/40 bg-white/60 p-4 shadow-lg backdrop-blur-sm">
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <input name="search" type="search" value="{{ request('search') }}" placeholder="Search title..." class="rounded-xl border-gray-200 bg-white/50 text-sm shadow-sm transition focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white">
                    <select name="status" class="rounded-xl border-gray-200 bg-white/50 text-sm shadow-sm transition focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white">
                        <option value="">All statuses</option>
                        @foreach (['Todo', 'In Progress', 'Review', 'Completed', 'Cancelled'] as $status)
                            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                    <select name="priority" class="rounded-xl border-gray-200 bg-white/50 text-sm shadow-sm transition focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white">
                        <option value="">All priorities</option>
                        @foreach (['Low', 'Medium', 'High', 'Critical'] as $priority)
                            <option value="{{ $priority }}" @selected(request('priority') === $priority)>{{ $priority }}</option>
                        @endforeach
                    </select>
                    <select name="assignee" class="rounded-xl border-gray-200 bg-white/50 text-sm shadow-sm transition focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white">
                        <option value="">All assignees</option>
                        @foreach ($assignees as $assignee)
                            <option value="{{ $assignee->id }}" @selected((string) request('assignee') === (string) $assignee->id)>{{ $assignee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-[10px] font-medium uppercase tracking-wider text-gray-400">Due date from</label>
                        <input name="due_date_from" type="date" value="{{ request('due_date_from') }}" class="w-full rounded-xl border-gray-200 bg-white/50 text-sm shadow-sm transition focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white">
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-medium uppercase tracking-wider text-gray-400">Due date to</label>
                        <input name="due_date_to" type="date" value="{{ request('due_date_to') }}" class="w-full rounded-xl border-gray-200 bg-white/50 text-sm shadow-sm transition focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white">
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-medium uppercase tracking-wider text-gray-400">Sort by</label>
                        <select name="sort" class="w-full rounded-xl border-gray-200 bg-white/50 text-sm shadow-sm transition focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white">
                            <option value="due_date" @selected($currentSort === 'due_date')">Due date</option>
                            <option value="newest" @selected($currentSort === 'newest')">Newest first</option>
                            <option value="oldest" @selected($currentSort === 'oldest')">Oldest first</option>
                            <option value="priority" @selected($currentSort === 'priority')">Priority</option>
                            <option value="recently_updated" @selected($currentSort === 'recently_updated')">Recently updated</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-md transition hover:bg-indigo-500 hover:shadow-lg">Apply</button>
                        @if (collect(request()->query())->filter()->isNotEmpty())
                            <a href="{{ route('tasks.index') }}" class="rounded-xl border border-gray-200 bg-white/50 px-4 py-2 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-white/80 hover:text-gray-900">Reset</a>
                        @endif
                    </div>
                </div>
            </form>

            {{-- Task List --}}
            @php
                $taskStatusStyles = [
                    'Todo' => 'bg-gray-100 text-gray-600',
                    'In Progress' => 'bg-blue-100 text-blue-700',
                    'Review' => 'bg-amber-100 text-amber-700',
                    'Completed' => 'bg-emerald-100 text-emerald-700',
                    'Cancelled' => 'bg-red-100 text-red-600',
                ];
                $priorityDots = [
                    'Low' => 'bg-gray-400',
                    'Medium' => 'bg-blue-400',
                    'High' => 'bg-orange-400',
                    'Critical' => 'bg-red-500',
                ];
            @endphp

            @if ($tasks->count())
                <div class="overflow-x-auto rounded-2xl border border-white/40 bg-white/60 shadow-lg backdrop-blur-sm">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-white/40 text-left text-xs font-medium uppercase tracking-wider text-gray-400">
                                <th class="px-5 py-3">Task</th>
                                <th class="px-5 py-3">Project</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3">Priority</th>
                                <th class="px-5 py-3">Assignee</th>
                                <th class="px-5 py-3">Due Date</th>
                                <th class="px-5 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100/60">
                            @foreach ($tasks as $task)
                                <tr class="transition hover:bg-white/40">
                                    <td class="px-5 py-4 font-medium">
                                        <a href="{{ route('tasks.show', $task) }}" class="text-indigo-600 hover:text-indigo-500">{{ $task->title }}</a>
                                    </td>
                                    <td class="px-5 py-4 text-gray-600">{{ $task->project->name }}</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $taskStatusStyles[$task->status] ?? 'bg-gray-100 text-gray-600' }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $priorityDots[$task->priority] ?? 'bg-gray-400' }}"></span>
                                            {{ $task->status }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-gray-600">{{ $task->priority }}</td>
                                    <td class="px-5 py-4 text-gray-600">{{ $task->assignedUser?->name ?? 'Unassigned' }}</td>
                                    <td class="px-5 py-4 text-gray-600">{{ $task->due_date?->format('M j, Y') ?? '—' }}</td>
                                    <td class="px-5 py-4 text-right">
                                        @if ($task->status !== 'Completed')
                                            @can('complete', $task)
                                                <form method="POST" action="{{ route('tasks.complete', $task) }}" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="rounded-lg bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-600 transition hover:bg-emerald-100">Complete</button>
                                                </form>
                                            @endcan
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="rounded-2xl border border-white/40 bg-white/60 px-6 py-12 text-center shadow-lg backdrop-blur-sm">
                    <svg class="mx-auto mb-3 h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" /></svg>
                    <p class="text-sm text-gray-500">No tasks match the selected filters.</p>
                </div>
            @endif

            <div class="mt-6">{{ $tasks->links() }}</div>
        </div>
    </div>
</x-app-layout>

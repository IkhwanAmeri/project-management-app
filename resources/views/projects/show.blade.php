<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $project->name }}</h2>
                <p class="mt-1 text-sm text-gray-500">Owned by {{ $project->owner->name }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('projects.kanban', $project) }}" class="rounded-xl border border-gray-200 bg-white/50 px-4 py-2 text-sm font-medium text-gray-600 shadow-sm transition hover:bg-white/80 hover:text-gray-900">Board</a>
                @can('update', $project)
                    <a href="{{ route('projects.edit', $project) }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-md transition hover:bg-indigo-500 hover:shadow-lg">Edit</a>
                @endcan
                @can('delete', $project)
                    <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Delete this project?')">
                        @csrf
                        @method('DELETE')
                        <button class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-md transition hover:bg-red-500 hover:shadow-lg">Delete</button>
                    </form>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-indigo-100 via-purple-50 to-pink-50 py-12">
        <div class="mx-auto grid max-w-7xl gap-6 sm:px-6 lg:grid-cols-3 lg:px-8">

            {{-- Project Details --}}
            <section class="rounded-2xl border border-white/40 bg-white/60 p-6 shadow-lg backdrop-blur-sm lg:col-span-2">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">Project Details</h3>
                    @php
                        $statusStyles = [
                            'planning' => 'bg-amber-100 text-amber-700',
                            'active' => 'bg-blue-100 text-blue-700',
                            'completed' => 'bg-emerald-100 text-emerald-700',
                            'cancelled' => 'bg-gray-100 text-gray-500',
                        ];
                    @endphp
                    <span class="rounded-full px-2.5 py-0.5 text-[10px] font-semibold capitalize {{ $statusStyles[$project->status] ?? 'bg-gray-100 text-gray-500' }}">{{ $project->status }}</span>
                </div>

                <dl class="mt-6 grid gap-6 text-sm">
                    <div>
                        <dt class="text-xs font-medium uppercase tracking-wider text-gray-400">Description</dt>
                        <dd class="mt-1 whitespace-pre-line text-gray-900">{{ $project->description ?: 'No description provided.' }}</dd>
                    </div>
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wider text-gray-400">Status</dt>
                            <dd class="mt-1 capitalize text-gray-900">{{ $project->status }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wider text-gray-400">Owner</dt>
                            <dd class="mt-1 text-gray-900">{{ $project->owner->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wider text-gray-400">Start Date</dt>
                            <dd class="mt-1 text-gray-900">{{ $project->start_date?->format('M j, Y') ?? 'Not set' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wider text-gray-400">End Date</dt>
                            <dd class="mt-1 text-gray-900">{{ $project->end_date?->format('M j, Y') ?? 'Not set' }}</dd>
                        </div>
                    </div>
                </dl>
            </section>

            {{-- Members --}}
            <aside class="rounded-2xl border border-white/40 bg-white/60 p-6 shadow-lg backdrop-blur-sm">
                <h3 class="text-base font-semibold text-gray-900">Members ({{ $project->members->count() }})</h3>
                <ul class="mt-4 space-y-3">
                    @php
                        $roleColors = ['Owner' => 'bg-indigo-100 text-indigo-700', 'Manager' => 'bg-amber-100 text-amber-700', 'Member' => 'bg-gray-100 text-gray-600'];
                        $avatarColors = ['bg-indigo-500', 'bg-emerald-500', 'bg-amber-500', 'bg-rose-500', 'bg-cyan-500', 'bg-violet-500'];
                    @endphp
                    @forelse ($project->members as $member)
                        <li class="flex items-center justify-between gap-3 rounded-xl px-3 py-2 transition hover:bg-white/60">
                            <div class="flex items-center gap-2.5">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $avatarColors[crc32($member->name) % count($avatarColors)] }} text-xs font-bold text-white">
                                    {{ strtoupper(substr($member->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $member->name }}</p>
                                    <p class="text-[10px] text-gray-400">Joined {{ $member->pivot->joined_at ? \Illuminate\Support\Carbon::parse($member->pivot->joined_at)->format('M j, Y') : '—' }}</p>
                                </div>
                            </div>
                            <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $roleColors[$member->pivot->role] ?? 'bg-gray-100 text-gray-600' }}">{{ $member->pivot->role }}</span>
                        </li>
                    @empty
                        <li class="py-3 text-center text-sm text-gray-500">No members yet.</li>
                    @endforelse
                </ul>
            </aside>

            {{-- Tasks --}}
            <section class="rounded-2xl border border-white/40 bg-white/60 p-6 shadow-lg backdrop-blur-sm lg:col-span-3">
                <div class="flex items-center justify-between gap-4">
                    <h3 class="text-base font-semibold text-gray-900">Tasks</h3>
                    @can('createTask', $project)
                        <a href="{{ route('projects.tasks.create', $project) }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-md transition hover:bg-indigo-500 hover:shadow-lg">Create Task</a>
                    @endcan
                </div>

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

                @if ($project->tasks->count())
                    @php
                        $totalTasks = $project->tasks->count();
                        $completedTasks = $project->tasks->where('status', 'Completed')->count();
                        $remainingTasks = $totalTasks - $completedTasks;
                        $progressPercent = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;
                    @endphp

                    <div class="mt-5 rounded-xl bg-white/40 p-4">
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center gap-3">
                                <span class="text-gray-500">{{ $completedTasks }} of {{ $totalTasks }} tasks completed</span>
                                @if ($remainingTasks > 0)
                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700">{{ $remainingTasks }} remaining</span>
                                @else
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">All done!</span>
                                @endif
                            </div>
                            <span class="font-semibold {{ $progressPercent === 100 ? 'text-emerald-600' : 'text-indigo-600' }}">{{ $progressPercent }}%</span>
                        </div>
                        <div class="mt-3 h-2.5 overflow-hidden rounded-full bg-gray-100">
                            <div class="h-full rounded-full transition-all duration-500 {{ $progressPercent === 100 ? 'bg-emerald-500' : 'bg-indigo-500' }}" style="width: {{ $progressPercent }}%"></div>
                        </div>
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-white/40 text-left text-xs font-medium uppercase tracking-wider text-gray-400">
                                    <th class="px-4 py-3">Task</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Priority</th>
                                    <th class="px-4 py-3">Assigned To</th>
                                    <th class="px-4 py-3">Due Date</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100/60">
                                @foreach ($project->tasks as $task)
                                    <tr class="transition hover:bg-white/40">
                                        <td class="px-4 py-3 font-medium">
                                            <a href="{{ route('tasks.show', $task) }}" class="text-indigo-600 hover:text-indigo-500">{{ $task->title }}</a>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $taskStatusStyles[$task->status] ?? 'bg-gray-100 text-gray-600' }}">
                                                <span class="h-1.5 w-1.5 rounded-full {{ $priorityDots[$task->priority] ?? 'bg-gray-400' }}"></span>
                                                {{ $task->status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-gray-600">{{ $task->priority }}</td>
                                        <td class="px-4 py-3 text-gray-600">{{ $task->assignedUser?->name ?? 'Unassigned' }}</td>
                                        <td class="px-4 py-3 text-gray-600">{{ $task->due_date?->format('M j, Y') ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="mt-4 rounded-xl border border-dashed border-gray-200 px-6 py-8 text-center">
                        <svg class="mx-auto mb-2 h-8 w-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" /></svg>
                        <p class="text-sm text-gray-500">No tasks yet.</p>
                        <a href="{{ route('projects.tasks.create', $project) }}" class="mt-2 inline-flex items-center gap-1 text-sm font-semibold text-indigo-600 hover:text-indigo-500">Create first task &rarr;</a>
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>

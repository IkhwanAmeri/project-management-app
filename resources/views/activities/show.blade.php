<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('activities.index') }}" class="rounded-xl border border-gray-200 bg-white/50 px-3 py-1.5 text-sm font-medium text-gray-500 shadow-sm transition hover:bg-white/80 hover:text-gray-700">&larr; Back</a>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Activity Details</h2>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-indigo-100 via-purple-50 to-pink-50 py-12">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">

            @php
                $actionConfig = [
                    'project_created'       => ['label' => 'Project Created', 'color' => 'bg-indigo-100 text-indigo-700'],
                    'task_created'          => ['label' => 'Task Created', 'color' => 'bg-blue-100 text-blue-700'],
                    'subtask_created'       => ['label' => 'Subtask Created', 'color' => 'bg-cyan-100 text-cyan-700'],
                    'task_status_changed'   => ['label' => 'Status Changed', 'color' => 'bg-amber-100 text-amber-700'],
                    'task_priority_changed' => ['label' => 'Priority Changed', 'color' => 'bg-orange-100 text-orange-700'],
                    'task_assignee_changed' => ['label' => 'Assignee Changed', 'color' => 'bg-violet-100 text-violet-700'],
                    'task_duplicated'       => ['label' => 'Task Duplicated', 'color' => 'bg-teal-100 text-teal-700'],
                    'task_deleted'          => ['label' => 'Task Deleted', 'color' => 'bg-red-100 text-red-700'],
                    'comment_added'         => ['label' => 'Comment Added', 'color' => 'bg-emerald-100 text-emerald-700'],
                    'file_uploaded'         => ['label' => 'File Uploaded', 'color' => 'bg-pink-100 text-pink-700'],
                ];
                $config = $actionConfig[$activity->action] ?? ['label' => $activity->action, 'color' => 'bg-gray-100 text-gray-600'];
            @endphp

            <div class="space-y-6">

                {{-- Activity Summary --}}
                <div class="rounded-2xl border border-white/40 bg-white/60 p-6 shadow-lg backdrop-blur-sm">
                    <div class="flex items-center gap-3">
                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $config['color'] }}">{{ $config['label'] }}</span>
                        <span class="text-sm text-gray-400">{{ $activity->created_at->format('M j, Y \a\t g:i A') }}</span>
                    </div>

                    <div class="mt-4">
                        <div class="flex items-center gap-2">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-500 text-xs font-bold text-white">
                                {{ strtoupper(substr($activity->user->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">{{ $activity->user->name }}</p>
                                <p class="text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </div>

                    @if ($activity->description)
                        <p class="mt-4 text-sm text-gray-600">{{ $activity->description }}</p>
                    @endif
                </div>

                {{-- Properties --}}
                @if ($activity->properties && count($activity->properties) > 0)
                    <div class="rounded-2xl border border-white/40 bg-white/60 p-6 shadow-lg backdrop-blur-sm">
                        <h3 class="text-sm font-semibold text-gray-900">Details</h3>

                        <div class="mt-4 space-y-3">
                            @foreach ($activity->properties as $key => $value)
                                <div class="flex items-center justify-between rounded-xl bg-white/40 px-4 py-2.5">
                                    <span class="text-xs font-medium uppercase tracking-wider text-gray-400">{{ ucwords(str_replace('_', ' ', $key)) }}</span>
                                    @if (is_array($value))
                                        <span class="text-sm text-gray-900">{{ json_encode($value) }}</span>
                                    @else
                                        <span class="text-sm text-gray-900">{{ $value }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Related Models --}}
                <div class="grid gap-6 sm:grid-cols-2">
                    {{-- Project --}}
                    @if ($activity->project)
                        <div class="rounded-2xl border border-white/40 bg-white/60 p-5 shadow-lg backdrop-blur-sm">
                            <h3 class="text-xs font-medium uppercase tracking-wider text-gray-400">Project</h3>
                            <a href="{{ route('projects.show', $activity->project) }}" class="mt-2 block text-sm font-semibold text-indigo-600 hover:text-indigo-500">{{ $activity->project->name }}</a>
                        </div>
                    @endif

                    {{-- Task --}}
                    @if ($activity->task)
                        <div class="rounded-2xl border border-white/40 bg-white/60 p-5 shadow-lg backdrop-blur-sm">
                            <h3 class="text-xs font-medium uppercase tracking-wider text-gray-400">Task</h3>
                            <a href="{{ route('tasks.show', $activity->task) }}" class="mt-2 block text-sm font-semibold text-indigo-600 hover:text-indigo-500">{{ $activity->task->title }}</a>
                        </div>
                    @endif
                </div>

                {{-- Timestamps --}}
                <div class="rounded-2xl border border-white/40 bg-white/60 p-5 shadow-lg backdrop-blur-sm">
                    <h3 class="text-xs font-medium uppercase tracking-wider text-gray-400">Timestamp</h3>
                    <p class="mt-2 text-sm text-gray-600">{{ $activity->created_at->format('l, F j, Y \a\t g:i:s A') }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

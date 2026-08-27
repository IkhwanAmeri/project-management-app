<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Task Detail</h2>
            <div class="flex flex-wrap gap-2">
                @can('createTask', $task->project)
                    <a href="{{ route('tasks.subtasks.create', $task) }}" class="rounded-xl bg-slate-600 px-4 py-2 text-sm font-semibold text-white shadow-md transition hover:bg-slate-500 hover:shadow-lg">Add Subtask</a>
                @endcan
                @can('createTask', $task->project)
                    <form method="POST" action="{{ route('tasks.duplicate', $task) }}" class="inline">
                        @csrf
                        <button class="rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-md transition hover:bg-violet-500 hover:shadow-lg">Duplicate</button>
                    </form>
                @endcan
                @if ($task->status !== 'Completed')
                    @can('complete', $task)
                        <form method="POST" action="{{ route('tasks.complete', $task) }}">
                            @csrf
                            @method('PATCH')
                            <button class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-md transition hover:bg-emerald-500 hover:shadow-lg">Mark Completed</button>
                        </form>
                    @endcan
                @endif
                @can('update', $task)
                    <a href="{{ route('tasks.edit', $task) }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-md transition hover:bg-indigo-500 hover:shadow-lg">Edit</a>
                @endcan
                @can('delete', $task)
                    <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Delete this task?')">
                        @csrf
                        @method('DELETE')
                        <button class="rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-md transition hover:bg-red-500 hover:shadow-lg">Delete</button>
                    </form>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-indigo-100 via-purple-50 to-pink-50 py-12">
        <div class="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">

            @if (session('status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/80 p-4 text-sm font-medium text-emerald-700 shadow-sm backdrop-blur-sm">{{ session('status') }}</div>
            @endif

            {{-- Task Details --}}
            <section class="rounded-2xl border border-white/40 bg-white/60 p-6 shadow-lg backdrop-blur-sm">
                @php
                    $statusStyles = [
                        'Todo' => 'bg-gray-100 text-gray-600',
                        'In Progress' => 'bg-blue-100 text-blue-700',
                        'Review' => 'bg-amber-100 text-amber-700',
                        'Completed' => 'bg-emerald-100 text-emerald-700',
                        'Cancelled' => 'bg-red-100 text-red-600',
                    ];
                    $priorityStyles = [
                        'Low' => 'bg-gray-100 text-gray-600',
                        'Medium' => 'bg-blue-100 text-blue-700',
                        'High' => 'bg-orange-100 text-orange-700',
                        'Critical' => 'bg-red-100 text-red-700',
                    ];
                @endphp

                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $task->title }}</h3>
                        @if ($task->parentTask)
                            <p class="mt-1 text-sm text-gray-500">Parent: <a href="{{ route('tasks.show', $task->parentTask) }}" class="font-medium text-indigo-600 hover:text-indigo-500">{{ $task->parentTask->title }}</a></p>
                        @endif
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        @can('changeStatus', $task)
                            <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                                <button @click="open = ! open" class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[10px] font-semibold {{ $statusStyles[$task->status] ?? 'bg-gray-100 text-gray-600' }} cursor-pointer transition hover:opacity-80">{{ $task->status }}</button>
                                <div x-show="open" x-transition class="absolute right-0 z-50 mt-1 w-40 rounded-xl border border-white/40 bg-white/90 shadow-xl backdrop-blur-md" style="display: none;">
                                    @foreach (['Todo', 'In Progress', 'Review', 'Completed', 'Cancelled'] as $status)
                                        <form method="POST" action="{{ route('tasks.status', $task) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $status }}">
                                            <button type="submit" class="block w-full px-4 py-2 text-left text-xs transition hover:bg-gray-100 {{ $task->status === $status ? 'font-semibold text-indigo-600' : 'text-gray-700' }}">{{ $status }}</button>
                                        </form>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[10px] font-semibold {{ $statusStyles[$task->status] ?? 'bg-gray-100 text-gray-600' }}">{{ $task->status }}</span>
                        @endcan

                        @can('changePriority', $task)
                            <div x-data="{ open: false }" @click.outside="open = false" class="relative">
                                <button @click="open = ! open" class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-semibold {{ $priorityStyles[$task->priority] ?? 'bg-gray-100 text-gray-600' }} cursor-pointer transition hover:opacity-80">{{ $task->priority }}</button>
                                <div x-show="open" x-transition class="absolute right-0 z-50 mt-1 w-32 rounded-xl border border-white/40 bg-white/90 shadow-xl backdrop-blur-md" style="display: none;">
                                    @foreach (['Low', 'Medium', 'High', 'Critical'] as $priority)
                                        <form method="POST" action="{{ route('tasks.priority', $task) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="priority" value="{{ $priority }}">
                                            <button type="submit" class="block w-full px-4 py-2 text-left text-xs transition hover:bg-gray-100 {{ $task->priority === $priority ? 'font-semibold text-indigo-600' : 'text-gray-700' }}">{{ $priority }}</button>
                                        </form>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-[10px] font-semibold {{ $priorityStyles[$task->priority] ?? 'bg-gray-100 text-gray-600' }}">{{ $task->priority }}</span>
                        @endcan
                    </div>
                </div>

                <div class="mt-6 border-t border-white/40 pt-6">
                    <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Description</p>
                    <p class="mt-1 whitespace-pre-line text-gray-900">{{ $task->description ?: 'No description provided.' }}</p>
                </div>

                <div class="mt-6 grid gap-6 border-t border-white/40 pt-6 text-sm sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Assigned To</p>
                        @can('assign', $task)
                            <div x-data="{ open: false }" @click.outside="open = false" class="relative mt-1">
                                <button @click="open = ! open" class="text-gray-900 transition hover:text-indigo-600 cursor-pointer">{{ $task->assignedUser?->name ?? 'Unassigned' }}</button>
                                <div x-show="open" x-transition class="absolute left-0 z-50 mt-1 w-48 rounded-xl border border-white/40 bg-white/90 shadow-xl backdrop-blur-md" style="display: none;">
                                    <form method="POST" action="{{ route('tasks.assign', $task) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="assigned_to" value="">
                                        <button type="submit" class="block w-full px-4 py-2 text-left text-xs text-gray-700 transition hover:bg-gray-100 {{ $task->assigned_to === null ? 'font-semibold text-indigo-600' : '' }}">Unassigned</button>
                                    </form>
                                    @foreach ($task->project->members as $member)
                                        <form method="POST" action="{{ route('tasks.assign', $task) }}">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="assigned_to" value="{{ $member->id }}">
                                            <button type="submit" class="block w-full px-4 py-2 text-left text-xs text-gray-700 transition hover:bg-gray-100 {{ (string) $task->assigned_to === (string) $member->id ? 'font-semibold text-indigo-600' : '' }}">{{ $member->name }}</button>
                                        </form>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <p class="mt-1 text-gray-900">{{ $task->assignedUser?->name ?? 'Unassigned' }}</p>
                        @endcan
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Created By</p>
                        <p class="mt-1 text-gray-900">{{ $task->creator->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Project</p>
                        <p class="mt-1 text-gray-900">{{ $task->project->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Start Date</p>
                        <p class="mt-1 text-gray-900">{{ $task->start_date?->format('M j, Y') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Due Date</p>
                        <p class="mt-1 text-gray-900">{{ $task->due_date?->format('M j, Y') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Completed</p>
                        <p class="mt-1 text-gray-900">{{ $task->completed_at?->format('M j, Y g:ia') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Estimated Hours</p>
                        <p class="mt-1 text-gray-900">{{ $task->estimated_hours ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-400">Actual Hours</p>
                        <p class="mt-1 text-gray-900">{{ $task->actual_hours ?? '—' }}</p>
                    </div>
                </div>
            </section>

            {{-- Subtasks --}}
            <section class="rounded-2xl border border-white/40 bg-white/60 p-6 shadow-lg backdrop-blur-sm">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">Subtasks</h3>
                    @can('createTask', $task->project)
                        <a href="{{ route('tasks.subtasks.create', $task) }}" class="text-sm font-semibold text-indigo-600 transition hover:text-indigo-500">+ Add Subtask</a>
                    @endcan
                </div>

                @if ($task->subtasks->count())
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">{{ $task->completedSubtasksCount() }} / {{ $task->subtasks->count() }} completed</span>
                            <span class="font-semibold text-gray-900">{{ $task->subtaskProgress() }}%</span>
                        </div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-gray-200">
                            <div class="h-full rounded-full bg-emerald-500 transition-all duration-500" style="width: {{ $task->subtaskProgress() }}%"></div>
                        </div>
                    </div>

                    <ul class="mt-4 divide-y divide-gray-100/60">
                        @foreach ($task->subtasks as $subtask)
                            <li class="flex items-center justify-between gap-4 py-3 transition hover:bg-white/40 rounded-xl px-2">
                                <div class="flex items-center gap-3">
                                    @if ($subtask->status === 'Completed')
                                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        </span>
                                    @else
                                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full border-2 border-gray-300"></span>
                                    @endif
                                    <a href="{{ route('tasks.show', $subtask) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 {{ $subtask->status === 'Completed' ? 'line-through text-gray-400' : '' }}">{{ $subtask->title }}</a>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-500">{{ $subtask->assignedUser?->name ?? 'Unassigned' }}</span>
                                    <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-600">{{ $subtask->status }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-4 text-sm text-gray-500">No subtasks yet.</p>
                @endif
            </section>

            {{-- Comments & Attachments --}}
            <div class="grid gap-6 lg:grid-cols-2">

                {{-- Comments --}}
                <section class="rounded-2xl border border-white/40 bg-white/60 p-6 shadow-lg backdrop-blur-sm">
                    <h3 class="text-base font-semibold text-gray-900">Comments</h3>
                    <form method="POST" action="{{ route('tasks.comments.store', $task) }}" class="mt-4">
                        @csrf
                        <textarea name="comment" rows="3" class="block w-full rounded-xl border-gray-200 bg-white/50 text-sm shadow-sm transition focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white" placeholder="Write a comment..." required>{{ old('comment') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('comment')" />
                        <div class="mt-3 text-right">
                            <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-md transition hover:bg-indigo-500 hover:shadow-lg">Add Comment</button>
                        </div>
                    </form>

                    @php
                        $avatarColors = ['bg-indigo-500', 'bg-emerald-500', 'bg-amber-500', 'bg-rose-500', 'bg-cyan-500', 'bg-violet-500'];
                    @endphp

                    @if ($task->comments->count())
                        <ul class="mt-6 space-y-4">
                            @foreach ($task->comments as $comment)
                                <li class="rounded-xl bg-white/40 p-4">
                                    <div class="flex items-start gap-3">
                                        <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full {{ $avatarColors[crc32($comment->user->name) % count($avatarColors)] }} text-[10px] font-bold text-white">
                                            {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between gap-2">
                                                <p class="text-sm font-medium text-gray-900">{{ $comment->user->name }}</p>
                                                <p class="shrink-0 text-xs text-gray-400">{{ $comment->created_at->diffForHumans() }}{{ $comment->edited_at ? ' · edited' : '' }}</p>
                                            </div>
                                            <p class="mt-1.5 whitespace-pre-line text-sm text-gray-600">{{ $comment->comment }}</p>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-4 text-sm text-gray-500">No comments yet.</p>
                    @endif
                </section>

                {{-- Attachments --}}
                <section class="rounded-2xl border border-white/40 bg-white/60 p-6 shadow-lg backdrop-blur-sm">
                    <h3 class="text-base font-semibold text-gray-900">Attachments</h3>
                    <form method="POST" action="{{ route('tasks.attachments.store', $task) }}" enctype="multipart/form-data" class="mt-4" x-data="{ filename: '' }">
                        @csrf
                        <div class="relative rounded-xl border-2 border-dashed border-gray-200 bg-white/30 p-4 text-center transition hover:border-indigo-300 hover:bg-white/50" @click="$refs.fileInput.click()">
                            <svg class="mx-auto mb-2 h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
                            <p class="text-xs text-gray-500" x-text="filename || 'Click to upload (max 10 MB)'"></p>
                            <input x-ref="fileInput" type="file" name="attachment" class="hidden" required @change="filename = $refs.fileInput.files[0]?.name || ''">
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('attachment')" />
                        <div class="mt-3 text-right">
                            <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-md transition hover:bg-indigo-500 hover:shadow-lg">Upload File</button>
                        </div>
                    </form>

                    @if ($task->attachments->count())
                        <ul class="mt-6 divide-y divide-gray-100/60">
                            @foreach ($task->attachments as $attachment)
                                <li class="flex items-center justify-between gap-3 py-3 transition hover:bg-white/40 rounded-xl px-2">
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ route('attachments.download', $attachment) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">{{ $attachment->original_name }}</a>
                                        <p class="mt-0.5 text-[10px] text-gray-400">Uploaded by {{ $attachment->user->name }} · {{ number_format($attachment->file_size / 1024, 1) }} KB</p>
                                    </div>
                                    <a href="{{ route('attachments.download', $attachment) }}" class="shrink-0 rounded-lg bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-600 transition hover:bg-indigo-100">Download</a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="mt-4 text-sm text-gray-500">No attachments yet.</p>
                    @endif
                </section>
            </div>

            <a href="{{ route('projects.show', $task->project) }}" class="inline-flex items-center gap-1 text-sm font-medium text-indigo-600 transition hover:text-indigo-500">&larr; Back to project</a>
        </div>
    </div>
</x-app-layout>

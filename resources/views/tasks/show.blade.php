<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Task Detail</h2>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('tasks.subtasks.create', $task) }}" class="rounded-md bg-slate-600 px-4 py-2 text-sm font-semibold text-white">Add Subtask</a>
                @if ($task->status !== 'Completed')
                    <form method="POST" action="{{ route('tasks.complete', $task) }}">
                        @csrf
                        @method('PATCH')
                        <button class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white">Mark Completed</button>
                    </form>
                @endif
                <a href="{{ route('tasks.edit', $task) }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Edit</a>
                <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Delete this task?')">
                    @csrf
                    @method('DELETE')
                    <button class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white">Delete</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            <section class="bg-white p-6 shadow-sm sm:rounded-lg">
                <dl class="grid gap-6 text-sm">
                    <div>
                        <dt class="font-medium text-gray-500">Task</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $task->title }}</dd>
                    </div>
                    @if ($task->parentTask)
                        <div>
                            <dt class="font-medium text-gray-500">Parent Task</dt>
                            <dd class="mt-1"><a href="{{ route('tasks.show', $task->parentTask) }}" class="font-medium text-indigo-600">{{ $task->parentTask->title }}</a></dd>
                        </div>
                    @endif
                    <div>
                        <dt class="font-medium text-gray-500">Description</dt>
                        <dd class="mt-1 whitespace-pre-line text-gray-900">{{ $task->description ?: 'No description provided.' }}</dd>
                    </div>
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div><dt class="font-medium text-gray-500">Priority</dt><dd class="mt-1 text-gray-900">{{ $task->priority }}</dd></div>
                        <div><dt class="font-medium text-gray-500">Status</dt><dd class="mt-1 text-gray-900">{{ $task->status }}</dd></div>
                        <div><dt class="font-medium text-gray-500">Assigned To</dt><dd class="mt-1 text-gray-900">{{ $task->assignedUser?->name ?? 'Unassigned' }}</dd></div>
                        <div><dt class="font-medium text-gray-500">Created By</dt><dd class="mt-1 text-gray-900">{{ $task->creator->name }}</dd></div>
                        <div><dt class="font-medium text-gray-500">Estimated Hours</dt><dd class="mt-1 text-gray-900">{{ $task->estimated_hours ?? '—' }}</dd></div>
                        <div><dt class="font-medium text-gray-500">Actual Hours</dt><dd class="mt-1 text-gray-900">{{ $task->actual_hours ?? '—' }}</dd></div>
                    </div>
                </dl>
            </section>

            <section class="bg-white p-6 shadow-sm sm:rounded-lg">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900">Subtasks ({{ $task->subtasks->count() }})</h3>
                    <a href="{{ route('tasks.subtasks.create', $task) }}" class="text-sm font-medium text-indigo-600">Add Subtask</a>
                </div>
                <ul class="mt-4 divide-y divide-gray-200">
                    @forelse ($task->subtasks as $subtask)
                        <li class="flex items-center justify-between gap-4 py-3">
                            <a href="{{ route('tasks.show', $subtask) }}" class="text-sm font-medium text-indigo-600">{{ $subtask->title }}</a>
                            <span class="text-sm text-gray-500">{{ $subtask->status }} · {{ $subtask->assignedUser?->name ?? 'Unassigned' }}</span>
                        </li>
                    @empty
                        <li class="py-3 text-sm text-gray-500">No subtasks yet.</li>
                    @endforelse
                </ul>
            </section>

            <div class="grid gap-6 lg:grid-cols-2">
                <section class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="font-semibold text-gray-900">Comments</h3>
                    <form method="POST" action="{{ route('tasks.comments.store', $task) }}" class="mt-4">
                        @csrf
                        <textarea name="comment" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Write a comment..." required>{{ old('comment') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('comment')" />
                        <div class="mt-3 text-right"><x-primary-button>Add Comment</x-primary-button></div>
                    </form>
                    <ul class="mt-6 space-y-4">
                        @forelse ($task->comments as $comment)
                            <li class="rounded-md bg-gray-50 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $comment->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}{{ $comment->edited_at ? ' · edited' : '' }}</p>
                                </div>
                                <p class="mt-2 whitespace-pre-line text-sm text-gray-700">{{ $comment->comment }}</p>
                            </li>
                        @empty
                            <li class="text-sm text-gray-500">No comments yet.</li>
                        @endforelse
                    </ul>
                </section>

                <section class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="font-semibold text-gray-900">Attachments</h3>
                    <form method="POST" action="{{ route('tasks.attachments.store', $task) }}" enctype="multipart/form-data" class="mt-4">
                        @csrf
                        <input type="file" name="attachment" class="block w-full text-sm text-gray-700" required>
                        <p class="mt-2 text-xs text-gray-500">Maximum file size: 10 MB.</p>
                        <x-input-error class="mt-2" :messages="$errors->get('attachment')" />
                        <div class="mt-3 text-right"><x-primary-button>Upload File</x-primary-button></div>
                    </form>
                    <ul class="mt-6 divide-y divide-gray-200">
                        @forelse ($task->attachments as $attachment)
                            <li class="flex items-center justify-between gap-3 py-3">
                                <div>
                                    <a href="{{ route('attachments.download', $attachment) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">{{ $attachment->original_name }}</a>
                                    <p class="mt-1 text-xs text-gray-500">Uploaded by {{ $attachment->user->name }} · {{ number_format($attachment->file_size / 1024, 1) }} KB</p>
                                </div>
                                <a href="{{ route('attachments.download', $attachment) }}" class="text-xs font-medium text-indigo-600">Download</a>
                            </li>
                        @empty
                            <li class="py-3 text-sm text-gray-500">No attachments yet.</li>
                        @endforelse
                    </ul>
                </section>
            </div>

            <a href="{{ route('projects.show', $task->project) }}" class="inline-block text-sm font-medium text-indigo-600">Back to project</a>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">{{ $project->name }}</h2>
                <p class="mt-1 text-sm text-gray-500">Owned by {{ $project->owner->name }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('projects.edit', $project) }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Edit</a>
                <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Delete this project?')">
                    @csrf
                    @method('DELETE')
                    <button class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500">Delete</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto grid max-w-7xl gap-6 sm:px-6 lg:grid-cols-3 lg:px-8">
            <section class="bg-white p-6 shadow-sm sm:rounded-lg lg:col-span-2 dark:bg-gray-800">
                <div class="flex items-center justify-between">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">Project Name</h3>
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium capitalize text-gray-700 dark:bg-gray-700 dark:text-gray-200">{{ $project->status }}</span>
                </div>
                <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $project->name }}</p>
                <dl class="mt-6 grid gap-6 border-t border-gray-200 pt-6 text-sm dark:border-gray-700">
                    <div><dt class="font-medium text-gray-500">Description</dt><dd class="mt-1 whitespace-pre-line text-gray-900 dark:text-gray-100">{{ $project->description ?: 'No description provided.' }}</dd></div>
                    <div><dt class="font-medium text-gray-500">Status</dt><dd class="mt-1 capitalize text-gray-900 dark:text-gray-100">{{ $project->status }}</dd></div>
                    <div><dt class="font-medium text-gray-500">Owner</dt><dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $project->owner->name }}</dd></div>
                    <div><dt class="font-medium text-gray-500">Dates</dt><dd class="mt-1 text-gray-900 dark:text-gray-100">{{ $project->start_date?->format('M j, Y') ?? 'Not set' }} — {{ $project->end_date?->format('M j, Y') ?? 'Not set' }}</dd></div>
                </dl>
            </section>

            <aside class="bg-white p-6 shadow-sm sm:rounded-lg dark:bg-gray-800">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">Members ({{ $project->members->count() }})</h3>
                <ul class="mt-4 space-y-4">
                    @forelse ($project->members as $member)
                        <li class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $member->name }}</p>
                                <p class="text-xs text-gray-500">Joined {{ $member->pivot->joined_at ? \Illuminate\Support\Carbon::parse($member->pivot->joined_at)->format('M j, Y') : '—' }}</p>
                            </div>
                            <span class="text-xs font-medium text-gray-600 dark:text-gray-300">{{ $member->pivot->role }}</span>
                        </li>
                    @empty
                        <li class="text-sm text-gray-500">No members yet.</li>
                    @endforelse
                </ul>
            </aside>

            <section class="bg-white p-6 shadow-sm sm:rounded-lg lg:col-span-3 dark:bg-gray-800">
                <div class="flex items-center justify-between gap-4">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">Tasks</h3>
                    <a href="{{ route('projects.tasks.create', $project) }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Create Task</a>
                </div>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead><tr><th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Task</th><th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Status</th><th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Assigned To</th><th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Due Date</th></tr></thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($project->tasks as $task)
                                <tr><td class="px-3 py-3 text-sm font-medium text-indigo-600"><a href="{{ route('tasks.show', $task) }}">{{ $task->title }}</a></td><td class="px-3 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $task->status }}</td><td class="px-3 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $task->assignedUser?->name ?? 'Unassigned' }}</td><td class="px-3 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $task->due_date?->format('M j, Y') ?? '—' }}</td></tr>
                            @empty
                                <tr><td colspan="4" class="px-3 py-6 text-center text-sm text-gray-500">No tasks yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>

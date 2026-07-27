<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Tasks</h2></x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-md bg-green-50 p-4 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            <form method="GET" class="mb-6 grid gap-4 rounded-lg bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-5 dark:bg-gray-800">
                <x-text-input name="search" type="search" placeholder="Search title" :value="request('search')" />
                <select name="status" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"><option value="">All statuses</option>@foreach (['Todo', 'In Progress', 'Review', 'Completed', 'Cancelled'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select>
                <select name="priority" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"><option value="">All priorities</option>@foreach (['Low', 'Medium', 'High', 'Critical'] as $priority)<option value="{{ $priority }}" @selected(request('priority') === $priority)>{{ $priority }}</option>@endforeach</select>
                <select name="assignee" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"><option value="">All assignees</option>@foreach ($assignees as $assignee)<option value="{{ $assignee->id }}" @selected((string) request('assignee') === (string) $assignee->id)>{{ $assignee->name }}</option>@endforeach</select>
                <div class="flex gap-2"><select name="sort" class="min-w-0 flex-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"><option value="due_asc" @selected(request('sort', 'due_asc') === 'due_asc')>Due date: earliest</option><option value="due_desc" @selected(request('sort') === 'due_desc')>Due date: latest</option></select><x-primary-button>Apply</x-primary-button></div>
            </form>

            <div class="overflow-x-auto bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50"><tr>@foreach (['Task', 'Project', 'Status', 'Priority', 'Assignee', 'Due Date', 'Action'] as $heading)<th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">{{ $heading }}</th>@endforeach</tr></thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($tasks as $task)
                            <tr><td class="px-5 py-4 text-sm font-medium"><a href="{{ route('tasks.show', $task) }}" class="text-indigo-600 hover:text-indigo-500">{{ $task->title }}</a></td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $task->project->name }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $task->status }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $task->priority }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $task->assignedUser?->name ?? 'Unassigned' }}</td><td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $task->due_date?->format('M j, Y') ?? '—' }}</td><td class="px-5 py-4 text-sm">@if ($task->status !== 'Completed')<form method="POST" action="{{ route('tasks.complete', $task) }}">@csrf @method('PATCH')<button class="font-medium text-green-600 hover:text-green-500">Complete</button></form>@endif</td></tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-8 text-center text-sm text-gray-500">No tasks match the selected filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-6">{{ $tasks->links() }}</div>
        </div>
    </div>
</x-app-layout>

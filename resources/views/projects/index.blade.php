<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Projects</h2>
            <a href="{{ route('projects.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">New project</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-md bg-green-50 p-4 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            <div class="overflow-x-auto bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            @foreach (['Project', 'Status', 'Start Date', 'End Date', 'Members', 'Action'] as $heading)
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-300">{{ $heading }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($projects as $project)
                            <tr>
                                <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $project->name }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm capitalize text-gray-600 dark:text-gray-300">{{ $project->status }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $project->start_date?->format('M j, Y') ?? '—' }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $project->end_date?->format('M j, Y') ?? '—' }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $project->members_count }}</td>
                                <td class="whitespace-nowrap px-6 py-4 text-sm"><a href="{{ route('projects.show', $project) }}" class="font-medium text-indigo-600 hover:text-indigo-500">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">No projects yet. Create your first project to get started.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">{{ $projects->links() }}</div>
        </div>
    </div>
</x-app-layout>

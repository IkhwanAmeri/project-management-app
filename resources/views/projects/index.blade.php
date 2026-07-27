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

            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                @forelse ($projects as $project)
                    <a href="{{ route('projects.show', $project) }}" class="block border-b border-gray-200 p-6 last:border-0 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/30">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $project->name }}</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $project->description ?: 'No description provided.' }}</p>
                                <p class="mt-2 text-xs text-gray-500">Owner: {{ $project->owner->name }}</p>
                            </div>
                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium capitalize text-gray-700 dark:bg-gray-700 dark:text-gray-200">{{ $project->status }}</span>
                        </div>
                    </a>
                @empty
                    <div class="p-6 text-sm text-gray-600 dark:text-gray-400">No projects yet. Create your first project to get started.</div>
                @endforelse
            </div>

            <div class="mt-6">{{ $projects->links() }}</div>
        </div>
    </div>
</x-app-layout>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <p class="text-lg font-medium">Welcome back, {{ auth()->user()->name }}.</p>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Your project workspace is ready.</p>
                </div>
            </div>

            <div class="mt-6 grid gap-6 sm:grid-cols-3">
                @foreach ([['Projects', 'Manage project work in one place.'], ['My tasks', 'Task tracking will appear here.'], ['Activity', 'Recent project activity will appear here.']] as [$title, $description])
                    <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100">{{ $title }}</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $description }}</p>
                    </div>
                @endforeach
            </div>
            </div>
        </div>
    </div>
</x-app-layout>

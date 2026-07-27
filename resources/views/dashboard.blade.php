<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($statistics as [$label, $value])
                    <div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $label }}</p>
                        <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $value }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">
                <div class="border-b border-gray-200 p-6 dark:border-gray-700">
                    <h3 class="font-semibold text-gray-900 dark:text-gray-100">My Tasks</h3>
                </div>
                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($tasks as $task)
                        <li class="flex items-center justify-between gap-4 p-6">
                            <div>
                                <a href="{{ route('tasks.show', $task) }}" class="font-medium text-indigo-600 hover:text-indigo-500">{{ $task->title }}</a>
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $task->project->name }}</p>
                            </div>
                            @if ($task->due_date)
                                <span @class([
                                    'text-sm',
                                    'text-red-600 dark:text-red-400' => $task->due_date->isPast() && ! $task->due_date->isToday(),
                                    'text-amber-600 dark:text-amber-400' => $task->due_date->isToday(),
                                    'text-gray-500 dark:text-gray-400' => $task->due_date->isFuture(),
                                ])>
                                    {{ $task->due_date->isToday() ? 'Due Today' : ($task->due_date->isTomorrow() ? 'Due Tomorrow' : 'Due '.$task->due_date->format('M j')) }}
                                </span>
                            @else
                                <span class="text-sm text-gray-500 dark:text-gray-400">No due date</span>
                            @endif
                        </li>
                    @empty
                        <li class="p-6 text-sm text-gray-500 dark:text-gray-400">You have no open tasks assigned to you.</li>
                    @endforelse
                </ul>
            </div>
            </div>
        </div>
    </div>
</x-app-layout>

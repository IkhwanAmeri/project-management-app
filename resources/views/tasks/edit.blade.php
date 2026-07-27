<x-app-layout>
    <x-slot name="header"><h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">Edit Task</h2></x-slot>
    <div class="py-12"><div class="mx-auto max-w-3xl sm:px-6 lg:px-8"><form method="POST" action="{{ route('tasks.update', $task) }}" class="bg-white p-6 shadow-sm sm:rounded-lg dark:bg-gray-800">@csrf @method('PUT') @include('tasks._form', ['task' => $task]) <div class="mt-6 flex justify-end gap-3"><a href="{{ route('tasks.show', $task) }}" class="rounded-md px-4 py-2 text-sm text-gray-700 dark:text-gray-300">Cancel</a><x-primary-button>Save Changes</x-primary-button></div></form></div></div>
</x-app-layout>

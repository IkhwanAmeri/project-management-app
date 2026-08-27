<div class="space-y-6">
    <div>
        <x-input-label for="title" value="Title" />
        <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $task->title ?? '')" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('title')" />
    </div>

    <div>
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-xl border-gray-200 bg-white/50 text-sm shadow-sm transition focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white">{{ old('description', $task->description ?? '') }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>

    <div class="grid gap-6 sm:grid-cols-3">
        <div>
            <x-input-label for="priority" value="Priority" />
            <select id="priority" name="priority" class="mt-1 block w-full rounded-xl border-gray-200 bg-white/50 text-sm shadow-sm transition focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white">
                @foreach (['Low', 'Medium', 'High', 'Critical'] as $priority)
                    <option value="{{ $priority }}" @selected(old('priority', $task->priority ?? 'Medium') === $priority)>{{ $priority }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('priority')" />
        </div>

        <div>
            <x-input-label for="status" value="Status" />
            <select id="status" name="status" class="mt-1 block w-full rounded-xl border-gray-200 bg-white/50 text-sm shadow-sm transition focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white">
                @foreach (['Todo', 'In Progress', 'Review', 'Completed', 'Cancelled'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $task->status ?? 'Todo') === $status)>{{ $status }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('status')" />
        </div>

        <div>
            <x-input-label for="assigned_to" value="Assign To" />
            <select id="assigned_to" name="assigned_to" class="mt-1 block w-full rounded-xl border-gray-200 bg-white/50 text-sm shadow-sm transition focus:border-indigo-500 focus:ring-indigo-500 focus:bg-white">
                <option value="">Unassigned</option>
                @foreach ($members as $member)
                    <option value="{{ $member->id }}" @selected((string) old('assigned_to', $task->assigned_to ?? '') === (string) $member->id)>{{ $member->name }}</option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('assigned_to')" />
        </div>
    </div>

    <div class="grid gap-6 sm:grid-cols-3">
        <div>
            <x-input-label for="start_date" value="Start Date" />
            <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" :value="old('start_date', isset($task) && $task->start_date ? $task->start_date->format('Y-m-d') : '')" />
        </div>
        <div>
            <x-input-label for="due_date" value="Due Date" />
            <x-text-input id="due_date" name="due_date" type="date" class="mt-1 block w-full" :value="old('due_date', isset($task) && $task->due_date ? $task->due_date->format('Y-m-d') : '')" />
        </div>
        <div>
            <x-input-label for="estimated_hours" value="Estimated Hours" />
            <x-text-input id="estimated_hours" name="estimated_hours" type="number" min="0" step="0.01" class="mt-1 block w-full" :value="old('estimated_hours', $task->estimated_hours ?? '')" />
        </div>
    </div>
</div>

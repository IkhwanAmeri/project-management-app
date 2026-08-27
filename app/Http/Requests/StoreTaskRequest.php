<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    /**
     * Allow authenticated users to create tasks.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        $project = $this->route('project');
        if ($project) {
            return $user->can('createTask', $project);
        }

        $task = $this->route('task');
        if ($task) {
            return $user->can('createTask', $task->project);
        }

        return false;
    }

    /**
     * Validate the fields used to create a task.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $projectId = $this->route('project')?->id ?? $this->route('task')?->project_id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', Rule::in(['Low', 'Medium', 'High', 'Critical'])],
            'status' => ['required', Rule::in(['Todo', 'In Progress', 'Review', 'Completed', 'Cancelled'])],
            'assigned_to' => ['nullable', 'integer', Rule::exists('project_members', 'user_id')->where('project_id', $projectId)],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'estimated_hours' => ['nullable', 'numeric', 'min:0'],
            'actual_hours' => ['nullable', 'numeric', 'min:0'],
            'parent_task_id' => ['nullable', 'integer', Rule::exists('tasks', 'id')->where('project_id', $projectId)],
        ];
    }

    /**
     * Configure the validator to prevent self-parenting and deep nesting.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $parentId = $this->input('parent_task_id');
            $routeTask = $this->route('task');

            // When creating via subtask route, the parent is the route task itself.
            if ($routeTask && ! $parentId) {
                $parentId = $routeTask->id;
            }

            if (! $parentId) {
                return;
            }

            $editingTaskId = $this->route('task')?->id;

            if ($this->input('parent_task_id') && $editingTaskId && (int) $this->input('parent_task_id') === (int) $editingTaskId) {
                $validator->errors()->add('parent_task_id', 'A task cannot be its own parent.');
            }

            $parentTask = Task::find($parentId);

            if ($parentTask && $parentTask->parent_task_id !== null) {
                $validator->errors()->add('parent_task_id', 'Cannot nest subtasks deeper than one level.');
            }
        });
    }
}

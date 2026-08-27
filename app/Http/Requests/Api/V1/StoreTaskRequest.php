<?php

namespace App\Http\Requests\Api\V1;

use App\Models\Task;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends ApiRequest
{
    /**
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

            if ($routeTask && ! $parentId) {
                $parentId = $routeTask->id;
            }

            if (! $parentId) {
                return;
            }

            if ($this->input('parent_task_id') && $routeTask && (int) $this->input('parent_task_id') === (int) $routeTask->id) {
                $validator->errors()->add('parent_task_id', 'A task cannot be its own parent.');
            }

            $parentTask = Task::find($parentId);

            if ($parentTask && $parentTask->parent_task_id !== null) {
                $validator->errors()->add('parent_task_id', 'Cannot nest subtasks deeper than one level.');
            }
        });
    }
}

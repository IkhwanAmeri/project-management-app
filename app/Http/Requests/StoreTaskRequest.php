<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    /**
     * Allow authenticated users to create tasks.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
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
}

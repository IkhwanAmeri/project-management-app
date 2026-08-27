<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskStatusRequest extends FormRequest
{
    /**
     * Only owners and managers of the task's project may change status.
     */
    public function authorize(): bool
    {
        $task = $this->route('task');

        if (! $task) {
            return false;
        }

        return $this->user()?->can('changeStatus', $task) ?? false;
    }

    /**
     * Validate the incoming status value and confirm task belongs to the project.
     */
    public function rules(): array
    {
        $task = $this->route('task');
        $project = $this->route('project');

        return [
            'status' => [
                'required',
                'string',
                Rule::in(['Todo', 'In Progress', 'Review', 'Completed', 'Cancelled']),
            ],
        ];
    }

    /**
     * Ensure the task belongs to the specified project before validation.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $task = $this->route('task');
            $project = $this->route('project');

            if ($task && $project && (int) $task->project_id !== (int) $project->id) {
                $validator->errors()->add('status', 'The task does not belong to this project.');
            }
        });
    }
}

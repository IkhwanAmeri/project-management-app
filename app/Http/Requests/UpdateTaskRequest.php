<?php

namespace App\Http\Requests;

class UpdateTaskRequest extends StoreTaskRequest
{
    /**
     * Only owners and managers of the task's project may update it.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('task')) ?? false;
    }

    /**
     * Validate task updates using the same rules as task creation.
     */
    public function rules(): array
    {
        return parent::rules();
    }
}

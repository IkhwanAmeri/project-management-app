<?php

namespace App\Http\Requests;

class UpdateTaskRequest extends StoreTaskRequest
{
    /**
     * Validate task updates using the same rules as task creation.
     */
    public function rules(): array
    {
        return parent::rules();
    }
}

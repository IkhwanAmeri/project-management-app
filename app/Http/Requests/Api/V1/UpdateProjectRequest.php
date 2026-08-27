<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Validation\Rule;

class UpdateProjectRequest extends ApiRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['planning', 'active', 'completed', 'cancelled'])],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'members' => ['nullable', 'array'],
            'members.*.user_id' => ['required', 'integer', 'distinct', Rule::exists('users', 'id')],
            'members.*.role' => ['required', Rule::in(['Manager', 'Member'])],
        ];
    }
}

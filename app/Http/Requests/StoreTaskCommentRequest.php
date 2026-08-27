<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskCommentRequest extends FormRequest
{
    /**
     * Only project members may comment on a task.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('view', $this->route('task')) ?? false;
    }

    /**
     * Validate a new task comment.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'comment' => ['required', 'string', 'max:5000'],
        ];
    }
}

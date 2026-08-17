<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskCommentRequest extends FormRequest
{
    /**
     * Allow authenticated users to add task comments.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
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

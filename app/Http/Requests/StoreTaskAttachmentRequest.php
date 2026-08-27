<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskAttachmentRequest extends FormRequest
{
    /**
     * Only project members may upload files to a task.
     */
    public function authorize(): bool
    {
        return $this->user()?->can('view', $this->route('task')) ?? false;
    }

    /**
     * Validate a task attachment up to 10 MB.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'attachment' => ['required', 'file', 'max:10240'],
        ];
    }
}

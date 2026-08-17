<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskAttachmentRequest extends FormRequest
{
    /**
     * Allow authenticated users to upload task files.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
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

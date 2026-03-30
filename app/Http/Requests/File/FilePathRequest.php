<?php

namespace App\Http\Requests\File;

use Illuminate\Foundation\Http\FormRequest;

class FilePathRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'disk' => ['nullable', 'in:public,private'],
            'path' => ['required', 'string', 'max:1024'],
        ];
    }
}

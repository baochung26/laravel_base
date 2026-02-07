<?php

namespace App\Http\Requests\File;

use Illuminate\Foundation\Http\FormRequest;

class UploadFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:' . config('constants.files.max_size_kb', 10240),
                'mimes:' . implode(',', config('constants.files.allowed_mimes', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'])),
            ],
            'disk' => ['nullable', 'in:public,private'],
            'folder' => ['nullable', 'string', 'max:255'],
        ];
    }
}

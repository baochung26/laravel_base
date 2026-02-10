<?php

namespace App\Http\Requests\User;

use App\Support\Validation\AvatarValidation;
use Illuminate\Foundation\Http\FormRequest;

class UploadAvatarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'avatar' => AvatarValidation::requiredRules(),
        ];
    }
}

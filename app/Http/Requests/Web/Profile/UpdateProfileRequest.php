<?php

namespace App\Http\Requests\Web\Profile;

use App\Support\Validation\AvatarValidation;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Use a dedicated error bag so profile and password forms do not conflict.
     */
    protected $errorBag = 'profileUpdate';

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
            'name' => ['required', 'string', 'max:255'],
            'avatar' => AvatarValidation::nullableRules(),
        ];
    }

}

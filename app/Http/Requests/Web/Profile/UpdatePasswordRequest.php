<?php

namespace App\Http\Requests\Web\Profile;

use App\Helpers\PasswordRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    /**
     * Use a dedicated error bag so profile and password forms do not conflict.
     */
    protected $errorBag = 'passwordUpdate';

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
            'current_password' => ['required', 'string', 'current_password:web'],
            'password' => ['required', 'confirmed', PasswordRules::standard()],
        ];
    }

}

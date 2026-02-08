<?php

namespace App\Http\Requests\Web\Profile;

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
        $avatarMimes = implode(',', config('constants.uploads.allowed_avatar_mimes', ['jpeg', 'png', 'jpg', 'gif']));
        $maxAvatarKb = (int) config('constants.uploads.max_avatar_kb', 2048);

        return [
            'name' => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:' . $avatarMimes, 'max:' . $maxAvatarKb],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Tên là bắt buộc.',
            'avatar.image' => 'Avatar phải là file ảnh hợp lệ.',
            'avatar.mimes' => 'Định dạng ảnh không hợp lệ.',
            'avatar.max' => 'Kích thước ảnh tối đa là 2MB.',
        ];
    }
}

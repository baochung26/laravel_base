<?php

namespace App\Support\Validation;

class AvatarValidation
{
    /**
     * @return array<int, string>
     */
    public static function nullableRules(): array
    {
        $mimes = implode(',', config('constants.uploads.allowed_avatar_mimes', ['jpeg', 'png', 'jpg', 'gif']));
        $max = (int) config('constants.uploads.max_avatar_kb', 2048);

        return ['nullable', 'image', 'mimes:' . $mimes, 'max:' . $max];
    }

    /**
     * @return array<int, string>
     */
    public static function requiredRules(): array
    {
        return array_merge(['required'], array_values(array_filter(self::nullableRules(), fn (string $rule) => $rule !== 'nullable')));
    }

    /**
     * @return array<string, string>
     */
    public static function messages(string $field = 'avatar', string $lang = 'en'): array
    {
        if ($lang === 'vi') {
            return [
                "{$field}.image" => 'Avatar phải là file ảnh hợp lệ.',
                "{$field}.mimes" => 'Định dạng ảnh không hợp lệ.',
                "{$field}.max" => 'Kích thước ảnh tối đa là 2MB.',
            ];
        }

        return [
            "{$field}.image" => 'Avatar must be an image.',
            "{$field}.mimes" => 'Avatar has an invalid file type.',
            "{$field}.max" => 'Avatar must not exceed 2MB.',
        ];
    }
}

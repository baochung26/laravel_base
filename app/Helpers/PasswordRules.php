<?php

namespace App\Helpers;

use Illuminate\Validation\Rules\Password;

class PasswordRules
{
    /**
     * Get standard password validation rules.
     * 
     * Requirements:
     * - Minimum 8 characters
     * - At least one letter
     * - At least one number
     * - Mixed case (optional in development)
     */
    public static function standard(): Password
    {
        $rules = Password::min(8)
            ->letters()
            ->numbers();

        // Add mixed case and symbols in production
        if (\app()->environment('production')) {
            $rules->mixedCase()
                ->symbols()
                ->uncompromised(); // Check against Have I Been Pwned
        }

        return $rules;
    }

    /**
     * Get strong password validation rules (for admin/sensitive operations).
     */
    public static function strong(): Password
    {
        return Password::min(12)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols()
            ->uncompromised();
    }

    /**
     * Get basic password validation rules (for development/testing).
     */
    public static function basic(): Password
    {
        return Password::min(6);
    }
}

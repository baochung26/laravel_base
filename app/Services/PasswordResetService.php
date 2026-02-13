<?php

namespace App\Services;

use App\Exceptions\ValidationException;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class PasswordResetService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * Send password reset link.
     */
    public function sendResetLink(string $email): string
    {
        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw new ValidationException(__('messages.errors.password_reset_failed'));
        }

        return $status;
    }

    /**
     * Reset password.
     */
    public function resetPassword(string $email, string $token, string $password): bool
    {
        $status = Password::reset(
            [
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $password,
                'token' => $token,
            ],
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw new ValidationException(__('messages.errors.reset_token_invalid'));
        }

        return true;
    }
}

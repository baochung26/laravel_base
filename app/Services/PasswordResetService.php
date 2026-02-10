<?php

namespace App\Services;

use App\Exceptions\ValidationException;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

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
            throw new ValidationException('Unable to send password reset link. Please try again later.');
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
            throw new ValidationException('Invalid or expired reset token.');
        }

        return true;
    }

    /**
     * Send password reset link and return status for web flow.
     */
    public function sendResetLinkStatus(string $email): string
    {
        return Password::sendResetLink(['email' => $email]);
    }

    /**
     * Reset password and return status for web flow.
     */
    public function resetPasswordWithToken(string $email, string $token, string $password): string
    {
        return Password::reset(
            [
                'email' => $email,
                'password' => $password,
                'password_confirmation' => $password,
                'token' => $token,
            ],
            function (User $user, string $newPassword): void {
                $user->forceFill([
                    'password' => Hash::make($newPassword),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );
    }
}

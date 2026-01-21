<?php

namespace App\Http\Controllers;

use App\Exceptions\ValidationException;
use App\Http\Requests\Password\ChangePasswordRequest;
use App\Http\Requests\Password\ForgotPasswordRequest;
use App\Http\Requests\Password\ResetPasswordRequest;
use App\Services\PasswordResetService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PasswordController extends Controller
{
    public function __construct(
        protected UserService $userService,
        protected PasswordResetService $passwordResetService
    ) {
    }

    /**
     * Change authenticated user password.
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $data = $request->validated();

            $this->userService->changePassword(
                $user->id,
                $data['current_password'],
                $data['password']
            );

            return response()->json([
                'message' => 'Password changed successfully',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    /**
     * Send password reset link.
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $this->passwordResetService->sendResetLink($data['email']);

            return response()->json([
                'message' => 'Password reset link sent to your email.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    /**
     * Reset password with token.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $this->passwordResetService->resetPassword(
                $data['email'],
                $data['token'],
                $data['password']
            );

            return response()->json([
                'message' => 'Password reset successfully. You can now login with your new password.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }
}

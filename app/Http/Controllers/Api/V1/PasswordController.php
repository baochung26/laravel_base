<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\ValidationException;
use App\Http\Requests\Password\ChangePasswordRequest;
use App\Http\Requests\Password\ForgotPasswordRequest;
use App\Http\Requests\Password\ResetPasswordRequest;
use App\Services\PasswordResetService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Password",
 *     description="Password management endpoints"
 * )
 */
class PasswordController extends ApiController
{
    public function __construct(
        protected UserService $userService,
        protected PasswordResetService $passwordResetService
    ) {
    }

    /**
     * @OA\Post(
     *     path="/api/v1/password/change",
     *     summary="Change authenticated user password",
     *     tags={"Password"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password","password","password_confirmation"},
     *             @OA\Property(property="current_password", type="string", format="password", example="oldpassword"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password changed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password changed successfully")
     *         )
     *     )
     * )
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

            return $this->successResponse(null, 'Password changed successfully');
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/password/forgot",
     *     summary="Send password reset link",
     *     tags={"Password"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset link sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password reset link sent to your email.")
     *         )
     *     )
     * )
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $this->passwordResetService->sendResetLink($data['email']);

            return $this->successResponse(null, 'Password reset link sent to your email.');
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/password/reset",
     *     summary="Reset password with token",
     *     tags={"Password"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","token","password","password_confirmation"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="token", type="string", example="reset-token-from-email"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password reset successfully. You can now login with your new password.")
     *         )
     *     )
     * )
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

            return $this->successResponse(null, 'Password reset successfully. You can now login with your new password.');
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
}

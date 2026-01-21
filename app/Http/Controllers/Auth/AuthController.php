<?php

namespace App\Http\Controllers\Auth;

use App\DTOs\LoginDTO;
use App\DTOs\UserDTO;
use App\Exceptions\ValidationException as AppValidationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {
    }

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $userDTO = UserDTO::fromArray([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
            ]);

            $result = $this->authService->register($userDTO, 'user');

            return response()->json([
                'message' => 'User registered successfully',
                'user' => $result['user']->toArray(),
                'token' => $result['token'],
            ], 201);
        } catch (AppValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    /**
     * Login user and create token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $loginDTO = LoginDTO::fromArray([
                'email' => $request->email,
                'password' => $request->password,
            ]);

            $result = $this->authService->login($loginDTO);

            return response()->json([
                'message' => 'Login successful',
                'user' => $result['user']->toArray(),
                'token' => $result['token'],
                'permissions' => $result['permissions'],
                'roles' => $result['roles'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
                'errors' => $e->errors(),
            ], 422);
        }
    }

    /**
     * Logout user (Revoke the token).
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Get authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        $userDTO = $this->authService->me();

        return response()->json([
            'user' => $userDTO->toArray(),
            'permissions' => $userDTO->permissions ?? [],
            'roles' => $userDTO->roles ?? [],
        ]);
    }

    /**
     * Refresh token.
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $token = $this->authService->refresh();

            return response()->json([
                'message' => 'Token refreshed successfully',
                'token' => $token,
            ]);
        } catch (AppValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }
}

<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\DTOs\LoginDTO;
use App\DTOs\UserDTO;
use App\Exceptions\ValidationException as AppValidationException;
use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends ApiController
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

            // Get model with relations for Resource (result['user'] is UserDTO)
            $userModel = app(\App\Services\UserService::class)->getModelByIdWithRelations($result['user']->id);
            
            return $this->successResponse([
                'user' => new UserResource($userModel),
                'token' => $result['token'],
            ], 'User registered successfully', 201);
        } catch (AppValidationException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Login user.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $loginDTO = LoginDTO::fromArray([
                'email' => $request->email,
                'password' => $request->password,
            ]);

            $result = $this->authService->login($loginDTO);

            // Get model with relations for Resource (result['user'] is UserDTO)
            $userModel = app(\App\Services\UserService::class)->getModelByIdWithRelations($result['user']->id);

            return $this->successResponse([
                'user' => new UserResource($userModel),
                'token' => $result['token'],
                'permissions' => $result['permissions'],
                'roles' => $result['roles'],
            ], 'Login successful');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors(), 'The provided credentials are incorrect.');
        }
    }

    /**
     * Logout user.
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout();

        return $this->successResponse(null, 'Logged out successfully');
    }

    /**
     * Get authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        $userDTO = $this->authService->me();
        $userModel = app(\App\Services\UserService::class)->getModelByIdWithRelations($userDTO->id);

        return $this->successResponse([
            'user' => new UserResource($userModel),
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

            return $this->successResponse(['token' => $token], 'Token refreshed successfully');
        } catch (AppValidationException $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }
}

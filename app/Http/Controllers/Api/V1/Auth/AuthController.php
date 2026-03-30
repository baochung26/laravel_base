<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\DTOs\LoginDTO;
use App\DTOs\UserDTO;
use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Requests\Auth\GoogleLoginRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends ApiController
{
    public function __construct(
        protected AuthService $authService,
        protected UserService $userService
    ) {
    }

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $userDTO = UserDTO::fromArray([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        $result = $this->authService->register($userDTO, 'user');

        return $this->authResponse($result, 'User registered successfully', 201);
    }

    /**
     * Login user.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $loginDTO = LoginDTO::fromArray([
            'email' => $request->email,
            'password' => $request->password,
        ]);

        $result = $this->authService->login($loginDTO);

        return $this->authResponse($result, 'Login successful');
    }

    /**
     * Login/register user by Google ID token.
     */
    public function googleLogin(GoogleLoginRequest $request): JsonResponse
    {
        $result = $this->authService->loginWithGoogle($request->string('id_token')->toString());

        return $this->authResponse($result, 'Google login successful');
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
        $userModel = $this->userService->getModelByIdWithRelations($userDTO->id);

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
        $tokens = $this->authService->refresh();

        return $this->successResponse($tokens, 'Token refreshed successfully');
    }

    private function authResponse(array $result, string $message, int $statusCode = 200): JsonResponse
    {
        $userModel = $this->userService->getModelByIdWithRelations($result['user']->id);

        return $this->successResponse([
            'user' => new UserResource($userModel),
            'token' => $result['token'],
            'access_token' => $result['access_token'] ?? $result['token'],
            'refresh_token' => $result['refresh_token'] ?? null,
            'permissions' => $result['permissions'] ?? [],
            'roles' => $result['roles'] ?? [],
            'login_type' => $result['login_type'] ?? 'password',
        ], $message, $statusCode);
    }
}

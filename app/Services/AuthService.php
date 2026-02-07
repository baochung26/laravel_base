<?php

namespace App\Services;

use App\DTOs\LoginDTO;
use App\DTOs\UserDTO;
use App\Models\User;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\ValidationException;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException as LaravelValidationException;
use Spatie\Permission\Models\Role;

class AuthService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected UserService $userService,
        protected GoogleTokenVerifier $googleTokenVerifier
    ) {
    }

    /**
     * Register a new user.
     */
    public function register(UserDTO $userDTO, ?string $roleName = 'user'): array
    {
        // Check rate limit for registration
        $key = 'register.' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            throw new ValidationException('Too many registration attempts. Please try again in ' . ceil($seconds / 60) . ' minute(s).');
        }

        // Check if email already exists
        if ($this->userRepository->existsByEmail($userDTO->email)) {
            RateLimiter::hit($key, 600); // 10 minutes lockout
            throw new ValidationException('Email already exists');
        }

        // Create user
        $createdUser = $this->userService->create($userDTO, $roleName);

        // Generate token
        $user = $this->userRepository->findOrFail($createdUser->id);
        $tokens = $this->issueTokenPair($user);

        // Clear rate limiter on success
        RateLimiter::clear($key);

        return [
            'user' => $createdUser,
            ...$tokens,
        ];
    }

    /**
     * Login user.
     */
    public function login(LoginDTO $loginDTO): array
    {
        // Use rate limiter for login attempts
        $key = 'login.' . $loginDTO->email . '|' . request()->ip();

        // Check rate limit for login attempts (5 attempts per 5 minutes)
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);

            throw LaravelValidationException::withMessages([
                'email' => ['Too many login attempts. Please try again in ' . ceil($seconds / 60) . ' minute(s).'],
            ]);
        }

        // Attempt authentication
        if (! Auth::attempt($loginDTO->toArray())) {
            RateLimiter::hit($key, 300); // 5 minutes lockout

            throw LaravelValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Clear rate limiter on successful login
        RateLimiter::clear($key);

        // Get authenticated user
        $user = Auth::user();
        $tokens = $this->issueTokenPair($user);

        // Load roles and permissions
        $userDTO = $this->userService->getByIdWithRelations($user->id);

        return [
            'user' => $userDTO,
            ...$tokens,
            'permissions' => $userDTO->permissions ?? [],
            'roles' => $userDTO->roles ?? [],
            'login_type' => 'password',
        ];
    }

    /**
     * Login user with Google ID token.
     */
    public function loginWithGoogle(string $idToken): array
    {
        $googleProfile = $this->googleTokenVerifier->verifyIdToken($idToken);

        if (! $googleProfile['email_verified']) {
            throw new UnauthorizedException('Google account email is not verified.');
        }

        $user = $this->userRepository->findBy('google_id', $googleProfile['google_id']);
        if (! $user) {
            $user = $this->userRepository->findByEmail($googleProfile['email']);
        }

        if (! $user) {
            $user = $this->userRepository->create([
                'name' => $googleProfile['name'],
                'email' => $googleProfile['email'],
                'google_id' => $googleProfile['google_id'],
                'avatar' => $googleProfile['avatar'],
                'email_verified_at' => Carbon::now(),
                'password' => Str::random(40),
            ]);

            $role = Role::where('name', 'user')->first();
            if ($role) {
                $user->assignRole($role);
            }
        } else {
            $updateData = [];

            if (! $user->google_id) {
                $updateData['google_id'] = $googleProfile['google_id'];
            }

            if ($googleProfile['avatar'] && ! $user->avatar) {
                $updateData['avatar'] = $googleProfile['avatar'];
            }

            if (! $user->email_verified_at && $googleProfile['email_verified']) {
                $updateData['email_verified_at'] = Carbon::now();
            }

            if (! empty($updateData)) {
                $this->userRepository->update($user->id, $updateData);
            }
        }

        $user = $this->userRepository->findOrFail($user->id);
        $tokens = $this->issueTokenPair($user);
        $userDTO = $this->userService->getByIdWithRelations($user->id);

        return [
            'user' => $userDTO,
            ...$tokens,
            'permissions' => $userDTO->permissions ?? [],
            'roles' => $userDTO->roles ?? [],
            'login_type' => 'google',
        ];
    }

    /**
     * Logout user.
     */
    public function logout(): bool
    {
        $user = Auth::user();
        if ($user) {
            $user->currentAccessToken()->delete();
            return true;
        }

        return false;
    }

    /**
     * Get authenticated user.
     */
    public function me(): UserDTO
    {
        $user = Auth::user();
        if (! $user) {
            throw new ValidationException('User not authenticated');
        }

        return $this->userService->getByIdWithRelations($user->id);
    }

    /**
     * Refresh token.
     */
    public function refresh(): array
    {
        $user = Auth::user();
        if (! $user) {
            throw new ValidationException('User not authenticated');
        }

        // Delete current token
        $user->currentAccessToken()->delete();

        return $this->issueTokenPair($user);
    }

    /**
     * Issue access + refresh token pair.
     */
    private function issueTokenPair(User $user): array
    {
        $accessToken = $user->createToken('auth_token')->plainTextToken;
        $refreshToken = $user->createToken('refresh_token')->plainTextToken;

        return [
            'token' => $accessToken, // backward compatibility
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ];
    }
}

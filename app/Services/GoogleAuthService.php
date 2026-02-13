<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class GoogleAuthService
{
    public function __construct(
        protected GoogleTokenVerifier $googleTokenVerifier
    ) {
    }

    /**
     * Authenticate or register user via Google ID token and log them in.
     *
     * @return array{user: User, is_new: bool}
     *
     * @throws \Illuminate\Validation\ValidationException
     * @throws \App\Exceptions\UnauthorizedException
     */
    public function authenticate(string $idToken): array
    {
        $googleProfile = $this->googleTokenVerifier->verifyIdToken($idToken);

        if (! $googleProfile['email_verified']) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'google' => __('messages.errors.google_email_not_verified'),
            ]);
        }

        $user = User::query()->where('google_id', $googleProfile['google_id'])->first();
        if (! $user) {
            $user = User::query()->where('email', $googleProfile['email'])->first();
        }

        if (! $user) {
            return [
                'user' => $this->createUser($googleProfile),
                'is_new' => true,
            ];
        }

        $this->updateUserIfNeeded($user, $googleProfile);

        return [
            'user' => $user,
            'is_new' => false,
        ];
    }

    /**
     * Log in the user and regenerate session.
     */
    public function login(User $user): void
    {
        Auth::guard('web')->login($user);
        request()->session()->regenerate();
    }

    protected function createUser(array $googleProfile): User
    {
        $user = User::query()->create([
            'name' => $googleProfile['name'],
            'email' => $googleProfile['email'],
            'google_id' => $googleProfile['google_id'],
            'avatar' => $googleProfile['avatar'],
            'email_verified_at' => Carbon::now(),
            'password' => Str::random(40),
        ]);

        $role = Role::query()->where('name', 'user')->first();
        if ($role) {
            $user->assignRole($role);
        }

        return $user;
    }

    protected function updateUserIfNeeded(User $user, array $googleProfile): void
    {
        $updateData = [];

        if (! $user->google_id) {
            $updateData['google_id'] = $googleProfile['google_id'];
        }
        if ($googleProfile['avatar'] && ! $user->avatar) {
            $updateData['avatar'] = $googleProfile['avatar'];
        }
        if (! $user->email_verified_at) {
            $updateData['email_verified_at'] = Carbon::now();
        }

        if (! empty($updateData)) {
            $user->update($updateData);
        }
    }
}

<?php

namespace App\Http\Controllers\Web\Auth;

use App\Exceptions\UnauthorizedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Auth\GoogleLoginRequest;
use App\Http\Requests\Web\Auth\LoginRequest;
use App\Models\User;
use App\Services\GoogleTokenVerifier;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        protected GoogleTokenVerifier $googleTokenVerifier
    ) {
    }

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()
            ->intended(route('dashboard', absolute: false))
            ->with('status', 'Đăng nhập thành công.');
    }

    public function google(GoogleLoginRequest $request): RedirectResponse
    {
        try {
            $googleProfile = $this->googleTokenVerifier->verifyIdToken(
                $request->string('id_token')->toString()
            );

            if (! $googleProfile['email_verified']) {
                throw ValidationException::withMessages([
                    'google' => 'Google account email is not verified.',
                ]);
            }

            $user = User::query()->where('google_id', $googleProfile['google_id'])->first();
            if (! $user) {
                $user = User::query()->where('email', $googleProfile['email'])->first();
            }

            if (! $user) {
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
            } else {
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

            Auth::guard('web')->login($user);
            $request->session()->regenerate();

            return redirect()
                ->intended(route('dashboard', absolute: false))
                ->with('status', 'Đăng nhập Google thành công.');
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (UnauthorizedException $exception) {
            throw ValidationException::withMessages([
                'google' => $exception->getMessage(),
            ]);
        } catch (HttpResponseException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages([
                'google' => 'Google login failed. Please try again.',
            ]);
        }
    }

    public function destroy(Request $request): RedirectResponse
    {
        auth()->guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Đăng xuất thành công.');
    }
}

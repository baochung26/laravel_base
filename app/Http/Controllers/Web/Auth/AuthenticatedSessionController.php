<?php

namespace App\Http\Controllers\Web\Auth;

use App\Exceptions\UnauthorizedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Auth\GoogleLoginRequest;
use App\Http\Requests\Web\Auth\LoginRequest;
use App\Services\GoogleAuthService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function __construct(
        protected GoogleAuthService $googleAuthService
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
            ->with('status', __('messages.success.login_success'));
    }

    public function google(GoogleLoginRequest $request): RedirectResponse
    {
        try {
            $result = $this->googleAuthService->authenticate(
                $request->string('id_token')->toString()
            );

            $this->googleAuthService->login($result['user']);

            return redirect()
                ->intended(route('dashboard', absolute: false))
                ->with('status', __('messages.success.google_login_success'));
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
                'google' => __('messages.errors.google_login_failed'),
            ]);
        }
    }

    public function destroy(Request $request): RedirectResponse
    {
        auth()->guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', __('messages.success.logout_success'));
    }
}

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
            ->intended(route('welcome', absolute: false))
            ->with('status', 'Đăng nhập thành công.');
    }

    public function google(GoogleLoginRequest $request): RedirectResponse
    {
        try {
            $result = $this->googleAuthService->authenticate(
                $request->string('id_token')->toString()
            );

            $this->googleAuthService->login($result['user']);

            return redirect()
                ->intended(route('welcome', absolute: false))
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

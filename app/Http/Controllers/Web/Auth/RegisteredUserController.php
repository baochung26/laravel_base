<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\Auth\RegisterRequest;
use App\Services\WebAuthService;
use App\DTOs\UserDTO;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(
        protected WebAuthService $webAuthService
    ) {
    }

    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $userDTO = UserDTO::fromArray($request->validated());
        $user = $this->webAuthService->register($userDTO);

        Auth::login($user);

        return redirect()->route('welcome')->with('status', 'Đăng ký tài khoản thành công.');
    }
}

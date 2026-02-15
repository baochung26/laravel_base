<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Support\WebRedirect;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route(WebRedirect::postAuthRouteName($request->user()));
        }

        $request->fulfill();

        return redirect()
            ->route(WebRedirect::postAuthRouteName($request->user()))
            ->with('status', 'Your email has been verified.');
    }
}

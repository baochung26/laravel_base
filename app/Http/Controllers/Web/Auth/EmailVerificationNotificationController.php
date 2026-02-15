<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Support\WebRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route(WebRedirect::postAuthRouteName($request->user()));
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'Verification link sent.');
    }
}

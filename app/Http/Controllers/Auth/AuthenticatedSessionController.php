<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        if (! $user->is_verified) {
            return $this->redirectToVerification($request, $user);
        }

        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            return redirect()->intended(route('v1.admin.dashboard', absolute: false));
        }

        return redirect()->intended(route('home', absolute: false));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    protected function redirectToVerification(Request $request, $user): RedirectResponse
    {
        if ($user->email) {
            $request->session()->put('email', $user->email);
            $user->sendEmailVerificationNotification();
        } elseif ($user->phone) {
            $request->session()->put('phone', $user->phone);
            $user->sendVerificationCode('phone');
        }

        return redirect()->route('auth.verify-code');
    }
}

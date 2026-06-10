<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class VerificationController extends Controller
{
    public function show(Request $request): View
    {
        return view('auth.verify-code');
    }

    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string'],
        ]);

        $email = $request->email ?? $request->session()->get('email');
        $phone = $request->phone ?? $request->session()->get('phone');

        if (! $email && ! $phone) {
            throw ValidationException::withMessages([
                'code' => [__('messages.Email or phone is required.')],
            ]);
        }

        $user = User::query()
            ->when($email, fn ($q) => $q->where('email', $email))
            ->when($phone && ! $email, fn ($q) => $q->where('phone', $phone))
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'code' => [__('messages.User not found.')],
            ]);
        }

        $type = $email ? 'email' : 'phone';

        $verification = Verification::query()
            ->where('user_id', $user->id)
            ->where('type', $type)
            ->where('code', $request->code)
            ->valid()
            ->first();

        if (! $verification) {
            throw ValidationException::withMessages([
                'code' => [__('messages.Invalid or expired code.')],
            ]);
        }

        $verification->markAsVerified();

        if ($type === 'email') {
            $user->markEmailAsVerified();
        } else {
            $user->phone_verified_at = now();
        }

        $user->is_verified = true;
        $user->save();

        if (! Auth::check()) {
            Auth::login($user);
        }

        $request->session()->forget(['email', 'phone']);

        if ($user->hasRole('admin')) {
            return redirect()->route('v1.admin.dashboard')
                ->with('status', __('messages.Email verified successfully.'));
        }

        return redirect()->route('home')
            ->with('status', __('messages.Email verified successfully.'));
    }

    public function resend(Request $request): RedirectResponse
    {
        $email = $request->input('email') ?? $request->session()->get('email');
        $phone = $request->input('phone') ?? $request->session()->get('phone');

        if (! $email && ! $phone) {
            return back()->withErrors([
                'code' => __('messages.Email or phone is required.'),
            ]);
        }

        $user = User::query()
            ->when($email, fn ($q) => $q->where('email', $email))
            ->when($phone && ! $email, fn ($q) => $q->where('phone', $phone))
            ->first();

        if (! $user) {
            return back()->withErrors([
                'code' => __('messages.User not found.'),
            ]);
        }

        if ($email) {
            $request->session()->put('email', $user->email);
            $user->sendEmailVerificationNotification();
        } else {
            $request->session()->put('phone', $user->phone);
            $user->sendVerificationCode('phone');
        }

        return back()->with('status', __('messages.Verification code resent successfully.'));
    }
}

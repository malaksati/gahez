<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'user',
            'is_active' => true,
            'is_verified' => false,
        ]);

        if (Role::where('name', 'user')->where('guard_name', 'web')->exists()) {
            $user->assignRole('user');
        }

        event(new Registered($user));

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

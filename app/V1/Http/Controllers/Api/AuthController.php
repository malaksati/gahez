<?php

namespace App\V1\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Verification;
use App\V1\Http\Requests\Api\Auth\LoginRequest;
use App\V1\Http\Requests\Api\Auth\RegisterRequest;
use App\V1\Http\Resources\Api\UserResource;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'birthdate' => $validated['birthdate'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'user',
            'is_active' => true,
            'is_verified' => false,
        ]);

        if (Role::where('name', 'user')->where('guard_name', 'web')->exists()) {
            $user->assignRole('user');
        }

        if ($user->email) {
            $user->sendEmailVerificationNotification();
        } elseif ($user->phone) {
            $user->sendVerificationCode('phone');
        }

        event(new Registered($user));

        $user->load('roles', 'permissions');

        return response()->json([
            'success' => true,
            'message' => __('messages.Registration successful. Please verify your account before logging in.'),
            'data' => [
                'user' => new UserResource($user),
            ],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        $user = Auth::user();
        $user->load('roles', 'permissions');

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => __('messages.Login successful.'),
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => __('messages.Logged out successfully.'),
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('roles', 'permissions');

        return response()->json([
            'success' => true,
            'data' => [
                'user' => new UserResource($user),
            ],
        ]);
    }

    public function resendVerificationCode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['sometimes', 'nullable', 'email', 'exists:users,email'],
            'phone' => ['sometimes', 'nullable', 'string', 'exists:users,phone'],
        ]);

        if (empty($data['email']) && empty($data['phone'])) {
            return response()->json([
                'success' => false,
                'message' => __('messages.Email or phone is required.'),
            ], 422);
        }

        $user = isset($data['email'])
            ? User::query()->where('email', $data['email'])->first()
            : User::query()->where('phone', $data['phone'])->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => __('messages.User not found.'),
            ], 404);
        }

        if ($user->email && ! empty($data['email'])) {
            $user->sendEmailVerificationNotification();
        } elseif ($user->phone && ! empty($data['phone'])) {
            $user->sendVerificationCode('phone');
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.Verification code resent successfully.'),
        ]);
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        return $this->verifyAccount($data['email'], null, 'email', $data['code']);
    }

    public function verifyPhone(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        return $this->verifyAccount(null, $data['phone'], 'phone', $data['code']);
    }

    protected function verifyAccount(?string $email, ?string $phone, string $type, string $code): JsonResponse
    {
        $user = User::query()
            ->when($email, fn ($q) => $q->where('email', $email))
            ->when($phone, fn ($q) => $q->where('phone', $phone))
            ->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => __('messages.User not found.'),
            ], 404);
        }

        if ($user->is_verified) {
            return response()->json([
                'success' => false,
                'message' => __('messages.Account already verified.'),
            ], 400);
        }

        $verification = Verification::query()
            ->where('user_id', $user->id)
            ->where('type', $type)
            ->where('code', $code)
            ->valid()
            ->first();

        if (! $verification) {
            return response()->json([
                'success' => false,
                'message' => __('messages.Invalid verification code.'),
            ], 400);
        }

        $verification->markAsVerified();

        if ($type === 'email') {
            $user->markEmailAsVerified();
        } else {
            $user->phone_verified_at = now();
        }

        $user->is_verified = true;
        $user->save();

        $user->load('roles', 'permissions');
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => __('messages.Account verified successfully.'),
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }
}

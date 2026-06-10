<?php

namespace App\V1\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ForgetPasswordMail;
use App\Models\User;
use App\V1\Http\Requests\Api\Auth\SetNewPasswordRequest;
use App\V1\Http\Resources\Api\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    public function resetPasswordSendCode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['nullable', 'email', 'exists:users,email'],
            'phone' => ['nullable', 'string', 'exists:users,phone'],
        ]);

        if (empty($data['email']) && empty($data['phone'])) {
            return response()->json([
                'success' => false,
                'message' => __('messages.Email or phone is required.'),
            ], 422);
        }

        $user = ! empty($data['email'])
            ? User::query()->where('email', $data['email'])->first()
            : User::query()->where('phone', $data['phone'])->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => __('messages.User not found.'),
            ], 404);
        }

        $code = (string) random_int(100000, 999999);

        DB::table('password_reset_tokens')->updateOrInsert(
            [
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            [
                'token' => $code,
                'created_at' => now(),
                'expires_at' => now()->addMinutes(10),
            ]
        );

        if ($user->email) {
            Mail::to($user->email)->send(new ForgetPasswordMail($code));
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.Password reset code sent successfully.'),
        ]);
    }

    public function resetPasswordVerifyCode(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['nullable', 'email', 'exists:users,email'],
            'phone' => ['nullable', 'string', 'exists:users,phone'],
            'code' => ['required', 'digits:6'],
        ]);

        if (empty($data['email']) && empty($data['phone'])) {
            return response()->json([
                'success' => false,
                'message' => __('messages.Email or phone is required.'),
            ], 422);
        }

        $user = ! empty($data['email'])
            ? User::query()->where('email', $data['email'])->first()
            : User::query()->where('phone', $data['phone'])->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => __('messages.User not found.'),
            ], 404);
        }

        $passwordResetToken = DB::table('password_reset_tokens')
            ->where(function ($query) use ($user) {
                if ($user->email) {
                    $query->where('email', $user->email);
                }
                if ($user->phone) {
                    $query->orWhere('phone', $user->phone);
                }
            })
            ->where('token', $data['code'])
            ->where('expires_at', '>', now())
            ->first();

        if (! $passwordResetToken) {
            return response()->json([
                'success' => false,
                'message' => __('messages.Invalid or expired verification code.'),
            ], 400);
        }

        $resetToken = (string) Str::uuid();

        DB::table('password_reset_tokens')
            ->where(function ($query) use ($user) {
                if ($user->email) {
                    $query->where('email', $user->email);
                }
                if ($user->phone) {
                    $query->orWhere('phone', $user->phone);
                }
            })
            ->update([
                'token' => $resetToken,
                'created_at' => now(),
                'expires_at' => now()->addMinutes(30),
            ]);

        return response()->json([
            'success' => true,
            'message' => __('messages.Password reset code verified successfully.'),
            'data' => [
                'reset_token' => $resetToken,
            ],
        ]);
    }

    public function resetPasswordSetNewPassword(SetNewPasswordRequest $request): JsonResponse
    {
        $data = $request->validated();

        $passwordResetToken = DB::table('password_reset_tokens')
            ->where('token', $data['reset_token'])
            ->where('expires_at', '>', now())
            ->first();

        if (! $passwordResetToken) {
            return response()->json([
                'success' => false,
                'message' => __('messages.Invalid or expired reset token.'),
            ], 400);
        }

        $user = User::query()
            ->when($passwordResetToken->email, fn ($q) => $q->where('email', $passwordResetToken->email))
            ->when($passwordResetToken->phone && ! $passwordResetToken->email, fn ($q) => $q->where('phone', $passwordResetToken->phone))
            ->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => __('messages.User not found.'),
            ], 404);
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        DB::table('password_reset_tokens')
            ->where('token', $data['reset_token'])
            ->delete();

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => __('messages.Password reset successfully. Please login with your new password.'),
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }
}

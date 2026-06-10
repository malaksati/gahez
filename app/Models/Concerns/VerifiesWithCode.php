<?php

namespace App\Models\Concerns;

use App\Mail\VerificationCodeMail;
use App\Models\Verification;
use Illuminate\Support\Facades\Mail;

trait VerifiesWithCode
{
    public function sendVerificationCode(string $type = 'phone'): void
    {
        $target = $type === 'email' ? $this->email ?? null : $this->phone ?? null;

        if (! $target) {
            return;
        }

        Verification::query()
            ->where('user_id', $this->id ?? null)
            ->where('type', $type)
            ->whereNull('verified_at')
            ->delete();

        $code = (string) random_int(100000, 999999);

        Verification::create([
            'user_id' => $this->id ?? null,
            'type' => $type,
            'target' => $target,
            'code' => $code,
            'expires_at' => now()->addMinutes(10),
        ]);

        if ($type === 'email') {
            Mail::to($this->email ?? null)->send(new VerificationCodeMail($code, $this));
        }
    }
}

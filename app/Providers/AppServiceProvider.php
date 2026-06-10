<?php

namespace App\Providers;

use App\Channels\ResilientMailChannel;
use App\Models\Verification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MailChannel::class, ResilientMailChannel::class);
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        VerifyEmail::createUrlUsing(function ($notifiable) {
            $code = (string) random_int(100000, 999999);

            Verification::create([
                'user_id' => $notifiable->id,
                'type' => 'email',
                'target' => $notifiable->email,
                'code' => $code,
                'expires_at' => now()->addMinutes(10),
            ]);

            return $code;
        });

        VerifyEmail::toMailUsing(function ($notifiable, $code) {
            return (new MailMessage)
                ->subject(__('messages.Email verification code'))
                ->view('emails.verify-code', [
                    'code' => $code,
                    'user' => $notifiable,
                ]);
        });
    }
}

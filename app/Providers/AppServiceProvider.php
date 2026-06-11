<?php

namespace App\Providers;

use App\Channels\ResilientMailChannel;
use App\Models\Verification;
use App\Translation\LocalizingTranslator;
use Illuminate\Translation\Translator;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MailChannel::class, ResilientMailChannel::class);

        $this->app->extend('translator', function (Translator $translator) {
            $localizing = new LocalizingTranslator(
                $translator->getLoader(),
                $translator->getLocale(),
            );
            $localizing->setFallback($translator->getFallback());

            return $localizing;
        });

        $this->syncMysqlTimezoneFromAppTimezone();
    }

    /**
     * MySQL on Laragon often lacks time_zone tables, so named zones like
     * "Africa/Cairo" fail. Map APP_TIMEZONE to a numeric offset for PDO.
     */
    protected function syncMysqlTimezoneFromAppTimezone(): void
    {
        if (env('DB_TIMEZONE') !== null) {
            return;
        }

        $this->app->booting(function () {
            try {
                $offset = (new \DateTimeImmutable('now', new \DateTimeZone(config('app.timezone'))))->format('P');

                config([
                    'database.connections.mysql.timezone' => $offset,
                    'database.connections.mariadb.timezone' => $offset,
                ]);
            } catch (\Throwable) {
                // Keep config/database.php default (+02:00).
            }
        });
    }

    public function boot(): void
    {
        Broadcast::routes([
            'middleware' => ['auth:sanctum', 'locale'],
            'prefix' => 'api/v1',
        ]);

        Broadcast::routes([
            'middleware' => ['web', 'auth', 'role:admin|super-admin'],
            'prefix' => 'admin',
        ]);

        Paginator::useBootstrapFive();

        Blade::directive('num', function (string $expression) {
            return "<?php echo e(local_num({$expression})); ?>";
        });

        Blade::directive('digits', function (string $expression) {
            return "<?php echo e(localize_digits({$expression})); ?>";
        });

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

<?php

namespace App\Notifications;

use App\Models\Coupon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CouponPromotionNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Coupon $coupon,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (! empty($notifiable->email)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('messages.New coupon available', ['app' => setting('app_name', config('app.name'))]))
            ->greeting(__('messages.Hello :name!', ['name' => $notifiable->name ?? __('messages.Customer')]))
            ->line(__('messages.Coupon promotion mail line', ['code' => $this->coupon->code]))
            ->line(__('messages.Coupon promotion mail cta'))
            ->line(__('messages.Thank you for being with us!'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'coupon_promotion',
            'title' => __('messages.New coupon available', ['app' => setting('app_name', config('app.name'))]),
            'message' => __('messages.Coupon promotion notification', ['code' => $this->coupon->code]),
            'coupon_id' => $this->coupon->id,
            'coupon_code' => $this->coupon->code,
            'coupon_type' => $this->coupon->type,
        ];
    }
}


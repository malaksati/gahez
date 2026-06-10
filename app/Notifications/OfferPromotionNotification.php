<?php

namespace App\Notifications;

use App\Models\Offer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OfferPromotionNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Offer $offer,
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
        $offerName = $this->offerName();

        return (new MailMessage)
            ->subject(__('messages.New offer available', ['app' => setting('app_name', config('app.name'))]))
            ->greeting(__('messages.Hello :name!', ['name' => $notifiable->name ?? __('messages.Customer')]))
            ->line(__('messages.Offer promotion mail line', ['offer' => $offerName]))
            ->line(__('messages.Offer promotion mail cta'))
            ->line(__('messages.Thank you for being with us!'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $offerName = $this->offerName();

        return [
            'type' => 'offer_promotion',
            'title' => __('messages.New offer available', ['app' => setting('app_name', config('app.name'))]),
            'message' => __('messages.Offer promotion notification', ['offer' => $offerName]),
            'offer_id' => $this->offer->id,
            'offer_name' => $offerName,
            'offer_type' => $this->offer->type,
        ];
    }

    protected function offerName(): string
    {
        $locale = app()->getLocale();

        return $this->offer->getTranslation('name', $locale, false)
            ?: $this->offer->getTranslation('name', 'en', false)
            ?: __('messages.Offer').' #'.$this->offer->id;
    }
}

<?php

namespace YourCompany\WhatsApp\Channels;

use Illuminate\Notifications\Notification;
use YourCompany\WhatsApp\WhatsAppClient;
use YourCompany\WhatsApp\Messages\WhatsAppTextMessage;
use YourCompany\WhatsApp\Messages\WhatsAppTemplateMessage;

class WhatsAppChannel
{
    protected WhatsAppClient $client;

    public function __construct(WhatsAppClient $client)
    {
        $this->client = $client;
    }

    /**
     * Send the given notification via WhatsApp Cloud API.
     *
     * @param mixed $notifiable
     * @param Notification $notification
     * @return array|null
     */
    public function send(mixed $notifiable, Notification $notification): ?array
    {
        if (! method_exists($notification, 'toWhatsApp')) {
            return null;
        }

        $to = method_exists($notifiable, 'routeNotificationFor')
            ? $notifiable->routeNotificationFor('whatsapp', $notification)
            : null;

        $to = $to ?? $notifiable->phone_number ?? $notifiable->phone ?? null;

        if (! $to) {
            return null;
        }

        $message = $notification->toWhatsApp($notifiable);

        if (is_string($message)) {
            $message = WhatsAppTextMessage::create($message);
        }

        if ($message instanceof WhatsAppTextMessage || $message instanceof WhatsAppTemplateMessage) {
            return $this->client->send($message->toArray($to));
        }

        if (is_array($message)) {
            return $this->client->send($message);
        }

        return null;
    }
}

# WhatsApp Notifier for Laravel / PHP

A lightweight Laravel/PHP wrapper package for sending WhatsApp messages using Meta's Cloud API.

## Directory Structure

```text
packages/whatsapp-notifier/
├── src/
│   ├── WhatsAppServiceProvider.php
│   ├── WhatsAppClient.php
│   ├── Channels/
│   │   └── WhatsAppChannel.php
│   ├── Messages/
│   │   ├── WhatsAppTextMessage.php
│   │   └── WhatsAppTemplateMessage.php
│   └── Exceptions/
│       └── WhatsAppApiException.php
├── config/
│   └── whatsapp.php
├── composer.json
└── README.md
```

## Installation

Add the local package to your Laravel app's `composer.json`:

```json
"repositories": [
    {
        "type": "path",
        "url": "packages/whatsapp-notifier"
    }
],
"require": {
    "your-company/whatsapp-notifier": "@dev"
}
```

Then run:

```bash
composer update your-company/whatsapp-notifier
php artisan vendor:publish --tag=whatsapp-config
```

## Environment Setup

Set your Meta WhatsApp credentials in `.env`:

```env
WHATSAPP_ACCESS_TOKEN=your_meta_access_token_here
WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id_here
WHATSAPP_API_VERSION=v20.0
```

## Direct Usage

### Send Template Message

```php
use YourCompany\WhatsApp\WhatsAppClient;

class OrderController extends Controller
{
    public function sendConfirmation(WhatsAppClient $wa)
    {
        $wa->sendTemplate(
            to: '94771234567',
            templateName: 'order_update',
            languageCode: 'en_US',
            components: [
                [
                    'type' => 'body',
                    'parameters' => [
                        ['type' => 'text', 'text' => '#ORD-9921']
                    ]
                ]
            ]
        );
    }
}
```

### Send Text Message

```php
use YourCompany\WhatsApp\WhatsAppClient;

$wa = app(WhatsAppClient::class);
$wa->sendText('94771234567', 'Hello from Laravel WhatsApp Notifier!');
```

## Laravel Notification Channel Usage

```php
use Illuminate\Notifications\Notification;
use YourCompany\WhatsApp\Channels\WhatsAppChannel;
use YourCompany\WhatsApp\Messages\WhatsAppTemplateMessage;

class OrderShippedNotification extends Notification
{
    public function via($notifiable): array
    {
        return [WhatsAppChannel::class];
    }

    public function toWhatsApp($notifiable)
    {
        return WhatsAppTemplateMessage::create('order_shipped')
            ->language('en_US')
            ->bodyParameters(['ORD-9921']);
    }
}
```

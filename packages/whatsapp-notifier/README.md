# WhatsApp Notifier Package for Laravel (nml/whatsapp-notifier)

NML WhatsApp Notifier is a reusable Laravel package for sending transactional and template messages via Meta Cloud API.

## Package Architecture

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

Add the package dependency to your main application `composer.json`:

```json
"repositories": [
    {
        "type": "path",
        "url": "packages/whatsapp-notifier"
    }
],
"require": {
    "nml/whatsapp-notifier": "@dev"
}
```

Run composer update and publish configuration:

```bash
composer update nml/whatsapp-notifier
php artisan vendor:publish --tag=whatsapp-config
```

## Environment Setup

Configure credentials in `.env`:

```env
WHATSAPP_ACCESS_TOKEN=your_meta_access_token_here
WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id_here
WHATSAPP_API_VERSION=v20.0
```

## Quick Reference

### Direct API Usage

```php
use Nml\WhatsApp\WhatsAppClient;

$wa = app(WhatsAppClient::class);

// Text Message
$wa->sendText('94771234567', 'Hello from WhatsApp Notifier!');

// Template Message
$wa->sendTemplate(
    to: '94771234567',
    templateName: 'hello_world',
    languageCode: 'en_US'
);
```

### Notification Channel Usage

```php
use Illuminate\Notifications\Notification;
use Nml\WhatsApp\Channels\WhatsAppChannel;
use Nml\WhatsApp\Messages\WhatsAppTemplateMessage;

class OrderNotification extends Notification
{
    public function via($notifiable): array
    {
        return [WhatsAppChannel::class];
    }

    public function toWhatsApp($notifiable)
    {
        return WhatsAppTemplateMessage::create('hello_world')
            ->language('en_US');
    }
}
```

## Testing

Run package tests:

```bash
php artisan test --filter=WhatsAppPackageTest
```

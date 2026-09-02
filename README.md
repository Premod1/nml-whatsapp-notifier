# NML WhatsApp Notifier

NML WhatsApp Notifier is a lightweight, standalone PHP and Laravel package designed for sending WhatsApp messages via Meta Cloud API. It provides a direct client for sending plain text and template messages, as well as a native Laravel Notification Channel for seamless application integration.

- Package Name: `nml/whatsapp-notifier`
- Namespace: `Nml\WhatsApp\`
- GitHub Repository: `https://github.com/Premod1/nml-whatsapp-notifier`
- Author Email: `premodsuraweera1@gmail.com`

## Features

- Meta WhatsApp Cloud API Integration: Native integration with Graph API v20.0+.
- Dual Messaging Modes: Supports sending both plain text messages and dynamic template messages.
- Native Laravel Notification Channel: Integrates directly into Laravel's notification system.
- Fluent Message Builders: Clean fluent API for building text (`WhatsAppTextMessage`) and template (`WhatsAppTemplateMessage`) payloads.
- Fully Tested: Includes unit test suites using Orchestra Testbench.

## Package Architecture

```text
nml-whatsapp-notifier/
├── config/
│   └── whatsapp.php
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
├── tests/
│   ├── TestCase.php
│   └── Unit/
│       └── WhatsAppPackageTest.php
├── composer.json
├── phpunit.xml
├── README.md
└── DOCUMENTATION.md
```

## Requirements

- PHP 8.2 or higher
- Laravel 10.x, 11.x, or 12.x
- GuzzleHTTP 7.x
- Meta Developer Account with WhatsApp Cloud API enabled

## Installation

### Option 1: Via Composer (Recommended)

Once published to Packagist, run:

```bash
composer require nml/whatsapp-notifier
```

### Option 2: Via Local Path (Development)

Add the local repository path to your application's `composer.json`:

```json
"repositories": [
    {
        "type": "path",
        "url": "../nml-whatsapp-notifier"
    }
],
"require": {
    "nml/whatsapp-notifier": "@dev"
}
```

Then run composer update:

```bash
composer update nml/whatsapp-notifier
```

### Publish Configuration

Publish the package configuration file to your Laravel app:

```bash
php artisan vendor:publish --tag=whatsapp-config
```

## Environment Configuration

Configure the required environment variables in your application `.env` file:

```env
WHATSAPP_ACCESS_TOKEN=your_meta_access_token_here
WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id_here
WHATSAPP_API_VERSION=v20.0
```

### Environment Variables Description

- `WHATSAPP_ACCESS_TOKEN`: Temporary access token or System User permanent token generated in Meta Developer Portal.
- `WHATSAPP_PHONE_NUMBER_ID`: Phone Number ID associated with your WhatsApp business app.
- `WHATSAPP_API_VERSION`: Meta Graph API version (default is `v20.0`).

## Usage Examples

### 1. Direct Usage via WhatsAppClient

#### Send Plain Text Message

```php
use Nml\WhatsApp\WhatsAppClient;

$wa = app(WhatsAppClient::class);

$response = $wa->sendText(
    to: '94771234567',
    text: 'Hello! Your order has been placed successfully.',
    previewUrl: true
);
```

#### Send Template Message

```php
use Nml\WhatsApp\WhatsAppClient;

$wa = app(WhatsAppClient::class);

$response = $wa->sendTemplate(
    to: '94771234567',
    templateName: 'jaspers_market_order_confirmation_v1',
    languageCode: 'en_US',
    components: [
        [
            'type' => 'body',
            'parameters' => [
                ['type' => 'text', 'text' => 'John Doe'],
                ['type' => 'text', 'text' => '123456'],
                ['type' => 'text', 'text' => 'Sep 2, 2026'],
            ]
        ]
    ]
);
```

### 2. Usage via Laravel Notification Channel

Create a notification class using Artisan:

```bash
php artisan make:notification OrderShippedNotification
```

Configure the notification class to use `WhatsAppChannel`:

```php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Nml\WhatsApp\Channels\WhatsAppChannel;
use Nml\WhatsApp\Messages\WhatsAppTemplateMessage;

class OrderShippedNotification extends Notification
{
    public function via($notifiable): array
    {
        return [WhatsAppChannel::class];
    }

    public function toWhatsApp($notifiable)
    {
        return WhatsAppTemplateMessage::create('jaspers_market_order_confirmation_v1')
            ->language('en_US')
            ->bodyParameters([
                $notifiable->name,
                $notifiable->order_id,
                now()->format('M j, Y'),
            ]);
    }
}
```

Define the recipient routing method in your User model:

```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    public function routeNotificationForWhatsApp($notification): string
    {
        return $this->phone_number;
    }
}
```

Dispatch the notification:

```php
$user->notify(new OrderShippedNotification());
```

## Testing

Run unit tests via PHPUnit:

```bash
vendor/bin/phpunit
```

## Publishing to Packagist

1. Push code to GitHub:
```bash
git init
git add .
git commit -m "Initial release of NML WhatsApp Notifier"
git branch -M main
git remote add origin https://github.com/Premod1/nml-whatsapp-notifier.git
git push -u origin main
```

2. Tag version release:
```bash
git tag -a v1.0.0 -m "Release version 1.0.0"
git push origin v1.0.0
```

3. Submit `https://github.com/Premod1/nml-whatsapp-notifier` on [Packagist.org](https://packagist.org/).

## Troubleshooting

### Error Code 131030: Recipient phone number not in allowed list

#### Cause
When using Meta WhatsApp Sandbox / Test Phone Number, Meta restricts outgoing messages strictly to recipient numbers that have been manually added and verified in the Meta Developer Portal.

#### Solution
1. Log in to [Meta Developer Portal](https://developers.facebook.com/).
2. Navigate to your App -> WhatsApp -> API Setup.
3. Locate the "To" phone number dropdown and click Manage phone number list.
4. Add the target phone number (with country code) and enter the OTP code received on that phone number.

## License

This package is open-source software licensed under the MIT License.

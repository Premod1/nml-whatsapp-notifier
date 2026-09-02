# NML WhatsApp Notifier Documentation

NML WhatsApp Notifier is an enterprise-ready PHP and Laravel package designed for sending transactional and marketing WhatsApp messages using Meta's WhatsApp Cloud API.

## Table of Contents

1. Overview
2. Features
3. Requirements
4. Installation and Setup
5. Environment Configuration
6. Package Architecture
7. API Reference
   - WhatsAppClient
   - WhatsAppTextMessage Builder
   - WhatsAppTemplateMessage Builder
   - WhatsAppChannel Driver
8. Laravel Notification Channel Integration
9. Artisan Command Interface
10. Automated Unit and Feature Testing
11. Meta Cloud API Setup and Authorization
12. Error Handling and Troubleshooting

---

## 1. Overview

NML WhatsApp Notifier provides a clean abstraction over Meta's Graph API. It handles HTTP authentication, payload generation, exception handling, and seamless integration into Laravel's notification subsystem.

---

## 2. Features

- Direct REST Client: Thin Guzzle HTTP wrapper targeting Meta Graph API endpoints.
- Template Messaging: Support for Meta approved templates with body parameters and language codes.
- Text Messaging: Support for plain text messaging with link previews.
- Native Notification Channel: Integrated driver for `Illuminate\Notifications\Notification`.
- Fluent Builders: Fluent builder interfaces for constructing structured message payloads.
- Console Test CLI: Custom Artisan command for sending text and template messages.
- Error Handling: Specialized exception handling for Graph API HTTP errors.

---

## 3. Requirements

- PHP: 8.2 or higher
- Framework: Laravel 10.x or 11.x
- HTTP Client: GuzzleHTTP 7.x
- Cloud Provider: Meta Developer Account with WhatsApp Cloud API access

---

## 4. Installation and Setup

### Step 1: Add Local Repository Path

Include the local package path in your root `composer.json`:

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

### Step 2: Install via Composer

Execute the composer update command:

```bash
composer update your-company/whatsapp-notifier
```

### Step 3: Publish Configuration File

Publish the configuration file to `config/whatsapp.php`:

```bash
php artisan vendor:publish --tag=whatsapp-config
```

---

## 5. Environment Configuration

Define the following key-value pairs in your environment file (`.env`):

```env
WHATSAPP_ACCESS_TOKEN=your_meta_access_token_here
WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id_here
WHATSAPP_API_VERSION=v20.0
```

### Parameter Reference

- `WHATSAPP_ACCESS_TOKEN`: Bearer token issued by Meta (System User Permanent Token recommended for production).
- `WHATSAPP_PHONE_NUMBER_ID`: Unique phone number ID generated inside Meta WhatsApp Dashboard.
- `WHATSAPP_API_VERSION`: Graph API version tag (e.g., `v20.0`).

---

## 6. Package Architecture

```text
packages/whatsapp-notifier/
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
├── composer.json
└── README.md
```

### Service Provider Registration

`WhatsAppServiceProvider` automatically registers `WhatsAppClient` as a singleton in the Laravel Service Container and merges default config settings from `config/whatsapp.php`.

---

## 7. API Reference

### WhatsAppClient

Class: `YourCompany\WhatsApp\WhatsAppClient`

#### Constructor
```php
public function __construct(array $config)
```

#### Methods

##### send(array $payload): array
Sends an arbitrary payload array directly to Meta Cloud API endpoint.

##### sendText(string $to, string $text, bool $previewUrl = false): array
Sends a text message to the target recipient.

##### sendTemplate(string $to, string $templateName, string $languageCode = 'en_US', array $components = []): array
Sends a template message with optional components and parameters.

---

### WhatsAppTextMessage Builder

Class: `YourCompany\WhatsApp\Messages\WhatsAppTextMessage`

#### Methods

- `public static function create(string $content = ''): static`
- `public function content(string $content): static`
- `public function previewUrl(bool $previewUrl = true): static`
- `public function toArray(string $to): array`

#### Example Usage

```php
use YourCompany\WhatsApp\Messages\WhatsAppTextMessage;

$message = WhatsAppTextMessage::create('Welcome to our service!')
    ->previewUrl(true);

$payload = $message->toArray('94771234567');
```

---

### WhatsAppTemplateMessage Builder

Class: `YourCompany\WhatsApp\Messages\WhatsAppTemplateMessage`

#### Methods

- `public static function create(string $name = ''): static`
- `public function name(string $name): static`
- `public function language(string $language): static`
- `public function bodyParameters(array $parameters): static`
- `public function components(array $components): static`
- `public function addComponent(array $component): static`
- `public function toArray(string $to): array`

#### Example Usage

```php
use YourCompany\WhatsApp\Messages\WhatsAppTemplateMessage;

$message = WhatsAppTemplateMessage::create('jaspers_market_order_confirmation_v1')
    ->language('en_US')
    ->bodyParameters(['John Doe', 'ORD-9921', 'Sep 2, 2026']);

$payload = $message->toArray('94771234567');
```

---

## 8. Laravel Notification Channel Integration

### Step 1: Create Notification Class

```php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use YourCompany\WhatsApp\Channels\WhatsAppChannel;
use YourCompany\WhatsApp\Messages\WhatsAppTemplateMessage;

class OrderConfirmedNotification extends Notification
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

### Step 2: Configure Notifiable Model

```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    public function routeNotificationForWhatsApp($notification): ?string
    {
        return $this->phone_number;
    }
}
```

### Step 3: Trigger Notification

```php
$user = User::find(1);
$user->notify(new OrderConfirmedNotification());
```

---

## 9. Artisan Command Interface

Command Signature: `whatsapp:test`

### Options and Arguments

- `phone` (Argument): Target recipient phone number with country code.
- `--type` (Option): Message type (`template` or `text`). Default: `template`.
- `--template` (Option): Meta template name. Default: `hello_world`.
- `--params` (Option): Comma-separated list of body parameters.
- `--lang` (Option): Template language code. Default: `en_US`.

### Command Examples

#### Test Default Hello World Template
```bash
php artisan whatsapp:test 94771234567
```

#### Test Text Message
```bash
php artisan whatsapp:test 94771234567 --type=text
```

#### Test Order Confirmation Template
```bash
php artisan whatsapp:test 94764686371 --template=jaspers_market_order_confirmation_v1 --params="John Doe, 123456, Sep 2 2026"
```

---

## 10. Automated Unit and Feature Testing

The package includes PHPUnit test cases in `tests/Unit/WhatsAppPackageTest.php`.

### Running Tests

Run full test suite:
```bash
php artisan test
```

Run specific package tests:
```bash
./vendor/bin/phpunit tests/Unit/WhatsAppPackageTest.php
```

---

## 11. Meta Cloud API Setup and Authorization

1. Register developer account at [developers.facebook.com](https://developers.facebook.com/).
2. Create a business app and select WhatsApp integration.
3. Obtain `Temporary Access Token` or create a `System User` for permanent access.
4. Copy `Phone Number ID` from the API Setup dashboard.
5. In Sandbox mode, add destination phone numbers under the "To" Phone Number dropdown and complete OTP verification.

---

## 12. Error Handling and Troubleshooting

### Error Code 131030: Recipient phone number not in allowed list

#### Diagnosis
Meta Test Phone Numbers restrict outgoing messages to explicit recipient numbers added to the developer account list.

#### Solution
Open Meta Developer Console -> WhatsApp -> API Setup -> To Dropdown -> Manage phone number list. Add target phone number and verify with 6-digit OTP code.

### Error Code 190: Invalid OAuth Access Token

#### Diagnosis
Access token has expired or lacks proper WhatsApp business permissions.

#### Solution
Generate a new token or configure a Meta System User token with `whatsapp_business_messaging` permission.

### Error Code 100: Parameter Value Not Valid

#### Diagnosis
Mismatched body parameter count or incorrect template name.

#### Solution
Verify template variable count matches parameter array elements passed in `bodyParameters()`.

# NML WhatsApp Notifier Documentation

NML WhatsApp Notifier is an enterprise-ready PHP and Laravel package designed for sending transactional and marketing WhatsApp messages using Meta's WhatsApp Cloud API.

Package Name: `nml/whatsapp-notifier`
PSR-4 Namespace: `Nml\WhatsApp\`
Repository: `https://github.com/Premod1/nml-whatsapp-notifier`
Author Email: `premodsuraweera1@gmail.com`

---

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
12. Publishing to Packagist Guide
13. Error Handling and Troubleshooting

---

## 1. Overview

NML WhatsApp Notifier provides a clean abstraction over Meta's Graph API. It handles HTTP authentication, payload generation, exception handling, and seamless integration into Laravel's notification subsystem.

---

## 2. Features

- Meta WhatsApp Cloud API Integration: Native integration with Graph API v20.0+.
- Dual Messaging Modes: Supports sending both plain text messages and dynamic template messages.
- Native Laravel Notification Channel: Integrated driver for `Illuminate\Notifications\Notification`.
- Fluent Message Builders: Clean fluent API for building text (`WhatsAppTextMessage`) and template (`WhatsAppTemplateMessage`) payloads.
- Console Test CLI: Custom Artisan command for sending text and template messages.
- Fully Tested: Includes unit test suites for container binding, payload formatting, and notification dispatching.

---

## 3. Requirements

- PHP: 8.2 or higher
- Framework: Laravel 10.x or 11.x
- HTTP Client: GuzzleHTTP 7.x
- Cloud Provider: Meta Developer Account with WhatsApp Cloud API access

---

## 4. Installation and Setup

### Option A: Installation via Composer (Once Published to Packagist)

```bash
composer require nml/whatsapp-notifier
```

### Option B: Local Repository Installation (Development Mode)

Include the local package path in your root `composer.json`:

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

Execute composer update:

```bash
composer update nml/whatsapp-notifier
```

### Publish Configuration File

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

### Service Provider Registration

`WhatsAppServiceProvider` automatically registers `WhatsAppClient` as a singleton in the Laravel Service Container under namespace `Nml\WhatsApp\WhatsAppClient` and merges default config settings from `config/whatsapp.php`.

---

## 7. API Reference

### WhatsAppClient

Class: `Nml\WhatsApp\WhatsAppClient`

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

Class: `Nml\WhatsApp\Messages\WhatsAppTextMessage`

#### Methods

- `public static function create(string $content = ''): static`
- `public function content(string $content): static`
- `public function previewUrl(bool $previewUrl = true): static`
- `public function toArray(string $to): array`

---

### WhatsAppTemplateMessage Builder

Class: `Nml\WhatsApp\Messages\WhatsAppTemplateMessage`

#### Methods

- `public static function create(string $name = ''): static`
- `public function name(string $name): static`
- `public function language(string $language): static`
- `public function bodyParameters(array $parameters): static`
- `public function components(array $components): static`
- `public function addComponent(array $component): static`
- `public function toArray(string $to): array`

---

## 8. Laravel Notification Channel Integration

### Step 1: Create Notification Class

```php
namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Nml\WhatsApp\Channels\WhatsAppChannel;
use Nml\WhatsApp\Messages\WhatsAppTemplateMessage;

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

### Examples

```bash
# Default Hello World Template
php artisan whatsapp:test 94771234567

# Text Message
php artisan whatsapp:test 94771234567 --type=text

# Order Confirmation Template
php artisan whatsapp:test 94764686371 --template=jaspers_market_order_confirmation_v1 --params="John Doe, 123456, Sep 2 2026"
```

---

## 10. Automated Unit and Feature Testing

```bash
php artisan test
```

---

## 11. Meta Cloud API Setup and Authorization

1. Register developer account at [developers.facebook.com](https://developers.facebook.com/).
2. Create a business app and select WhatsApp integration.
3. Obtain `Temporary Access Token` or create a `System User` for permanent access.
4. Copy `Phone Number ID` from the API Setup dashboard.
5. In Sandbox mode, add destination phone numbers under the "To" Phone Number dropdown and complete OTP verification.

---

## 12. Publishing to Packagist Guide

### Step 1: Configure composer.json
Package composer file (`packages/whatsapp-notifier/composer.json`) configured with:
- Name: `nml/whatsapp-notifier`
- Repository: `https://github.com/Premod1/nml-whatsapp-notifier`
- Author Email: `premodsuraweera1@gmail.com`

### Step 2: Push Package to GitHub
```bash
git init
git add .
git commit -m "Initial release of NML WhatsApp Notifier"
git branch -M main
git remote add origin https://github.com/Premod1/nml-whatsapp-notifier.git
git push -u origin main
```

### Step 3: Tag Release Version
```bash
git tag -a v1.0.0 -m "Release version 1.0.0"
git push origin v1.0.0
```

### Step 4: Submit to Packagist
1. Log in to [packagist.org](https://packagist.org/).
2. Click **Submit**.
3. Paste repository URL: `https://github.com/Premod1/nml-whatsapp-notifier`.
4. Click **Submit**.

---

## 13. Error Handling and Troubleshooting

### Error Code 131030: Recipient phone number not in allowed list
Open Meta Developer Console -> WhatsApp -> API Setup -> To Dropdown -> Manage phone number list. Add target phone number and verify with 6-digit OTP code.

### Error Code 190: Invalid OAuth Access Token
Generate a new token or configure a Meta System User token with `whatsapp_business_messaging` permission.

### Error Code 100: Parameter Value Not Valid
Verify template variable count matches parameter array elements passed in `bodyParameters()`.

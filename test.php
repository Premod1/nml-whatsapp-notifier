<?php

require __DIR__ . '/vendor/autoload.php';

// Read credentials from .env file
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$name, $value] = explode('=', $line, 2);
            $_ENV[trim($name)] = trim($value);
        }
    }
}

use Nml\WhatsApp\WhatsAppClient;

$client = new WhatsAppClient([
    'token' => $_ENV['WHATSAPP_ACCESS_TOKEN'] ?? '',
    'phone_number_id' => $_ENV['WHATSAPP_PHONE_NUMBER_ID'] ?? '',
    'api_version' => $_ENV['WHATSAPP_API_VERSION'] ?? 'v20.0',
]);

$targetPhone = $argv[1] ?? '94764686371';
$templateName = $argv[2] ?? 'hello_world';

try {
    echo "Sending template '{$templateName}' to phone: {$targetPhone}...\n";

    $response = $client->sendTemplate(
        to: $targetPhone,
        templateName: $templateName,
        languageCode: 'en_US'
    );

    echo "Message sent successfully!\n";
    print_r($response);
} catch (\Exception $e) {
    echo "Failed to send message: " . $e->getMessage() . "\n";
}

<?php

return [
    'token' => env('WHATSAPP_ACCESS_TOKEN'),
    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
    'api_version' => env('WHATSAPP_API_VERSION', 'v20.0'),
    'base_uri' => 'https://graph.facebook.com/',
];

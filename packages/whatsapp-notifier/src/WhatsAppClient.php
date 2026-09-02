<?php

namespace Nml\WhatsApp;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Nml\WhatsApp\Exceptions\WhatsAppApiException;
use Nml\WhatsApp\Messages\WhatsAppTextMessage;
use Nml\WhatsApp\Messages\WhatsAppTemplateMessage;

class WhatsAppClient
{
    protected Client $http;
    protected string $token;
    protected string $phoneNumberId;
    protected string $version;

    public function __construct(array $config)
    {
        $this->token = $config['token'] ?? '';
        $this->phoneNumberId = $config['phone_number_id'] ?? '';
        $this->version = $config['api_version'] ?? 'v20.0';
        $baseUri = rtrim($config['base_uri'] ?? 'https://graph.facebook.com/', '/') . '/';

        $this->http = new Client([
            'base_uri' => $baseUri . $this->version . '/' . $this->phoneNumberId . '/',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type'  => 'application/json',
            ],
            'timeout' => 15.0,
        ]);
    }

    /**
     * Send payload array directly to Meta Cloud API endpoint.
     *
     * @param array $payload
     * @return array
     * @throws WhatsAppApiException
     */
    public function send(array $payload): array
    {
        try {
            $response = $this->http->post('messages', [
                'json' => $payload,
            ]);

            return json_decode($response->getBody()->getContents(), true) ?? [];
        } catch (GuzzleException $e) {
            throw new WhatsAppApiException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Send simple text message.
     *
     * @param string $to
     * @param string $text
     * @param bool $previewUrl
     * @return array
     * @throws WhatsAppApiException
     */
    public function sendText(string $to, string $text, bool $previewUrl = false): array
    {
        $message = WhatsAppTextMessage::create($text)->previewUrl($previewUrl);
        return $this->send($message->toArray($to));
    }

    /**
     * Send template message.
     *
     * @param string $to
     * @param string $templateName
     * @param string $languageCode
     * @param array $components
     * @return array
     * @throws WhatsAppApiException
     */
    public function sendTemplate(string $to, string $templateName, string $languageCode = 'en_US', array $components = []): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $languageCode],
                'components' => $components,
            ],
        ];

        return $this->send($payload);
    }
}

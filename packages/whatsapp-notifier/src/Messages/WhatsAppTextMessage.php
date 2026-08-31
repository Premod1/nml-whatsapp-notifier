<?php

namespace YourCompany\WhatsApp\Messages;

class WhatsAppTextMessage
{
    protected string $content;
    protected bool $previewUrl = false;

    public static function create(string $content = ''): static
    {
        return new static($content);
    }

    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    public function content(string $content): static
    {
        $this->content = $content;
        return $this;
    }

    public function previewUrl(bool $previewUrl = true): static
    {
        $this->previewUrl = $previewUrl;
        return $this;
    }

    public function toArray(string $to): array
    {
        return [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $to,
            'type' => 'text',
            'text' => [
                'preview_url' => $this->previewUrl,
                'body' => $this->content,
            ],
        ];
    }
}

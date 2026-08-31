<?php

namespace YourCompany\WhatsApp\Messages;

class WhatsAppTemplateMessage
{
    protected string $name;
    protected string $language = 'en_US';
    protected array $components = [];

    public static function create(string $name = ''): static
    {
        return new static($name);
    }

    public function __construct(string $name = '')
    {
        $this->name = $name;
    }

    public function name(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function language(string $language): static
    {
        $this->language = $language;
        return $this;
    }

    public function components(array $components): static
    {
        $this->components = $components;
        return $this;
    }

    public function addComponent(array $component): static
    {
        $this->components[] = $component;
        return $this;
    }

    public function bodyParameters(array $parameters): static
    {
        $params = array_map(function ($param) {
            return is_array($param) ? $param : ['type' => 'text', 'text' => (string) $param];
        }, $parameters);

        $this->components[] = [
            'type' => 'body',
            'parameters' => $params,
        ];

        return $this;
    }

    public function toArray(string $to): array
    {
        return [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $this->name,
                'language' => ['code' => $this->language],
                'components' => $this->components,
            ],
        ];
    }
}

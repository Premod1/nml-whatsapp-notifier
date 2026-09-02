<?php

namespace Tests\Unit;

use Tests\TestCase;
use Nml\WhatsApp\WhatsAppClient;
use Nml\WhatsApp\Channels\WhatsAppChannel;
use Nml\WhatsApp\Messages\WhatsAppTextMessage;
use Nml\WhatsApp\Messages\WhatsAppTemplateMessage;
use Illuminate\Notifications\Notification;

class WhatsAppPackageTest extends TestCase
{
    public function test_whatsapp_client_is_bound_in_container(): void
    {
        $client = app(WhatsAppClient::class);
        $this->assertInstanceOf(WhatsAppClient::class, $client);
    }

    public function test_text_message_structure(): void
    {
        $message = WhatsAppTextMessage::create('Test message content')
            ->previewUrl(true);

        $payload = $message->toArray('94771234567');

        $this->assertEquals([
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => '94771234567',
            'type' => 'text',
            'text' => [
                'preview_url' => true,
                'body' => 'Test message content',
            ],
        ], $payload);
    }

    public function test_template_message_structure(): void
    {
        $message = WhatsAppTemplateMessage::create('order_update')
            ->language('en_US')
            ->bodyParameters(['#ORD-9921']);

        $payload = $message->toArray('94771234567');

        $this->assertEquals([
            'messaging_product' => 'whatsapp',
            'to' => '94771234567',
            'type' => 'template',
            'template' => [
                'name' => 'order_update',
                'language' => ['code' => 'en_US'],
                'components' => [
                    [
                        'type' => 'body',
                        'parameters' => [
                            ['type' => 'text', 'text' => '#ORD-9921']
                        ]
                    ]
                ],
            ],
        ], $payload);
    }

    public function test_notification_channel_send_formulation(): void
    {
        $mockClient = $this->createMock(WhatsAppClient::class);
        $mockClient->expects($this->once())
            ->method('send')
            ->with($this->callback(function ($payload) {
                return $payload['to'] === '94771234567'
                    && $payload['template']['name'] === 'order_update';
            }))
            ->willReturn(['messaging_product' => 'whatsapp', 'messages' => [['id' => 'wamid.123']]]);

        $channel = new WhatsAppChannel($mockClient);

        $notifiable = new class {
            public string $phone = '94771234567';
        };

        $notification = new class extends Notification {
            public function toWhatsApp($notifiable)
            {
                return WhatsAppTemplateMessage::create('order_update')
                    ->language('en_US')
                    ->bodyParameters(['#ORD-9921']);
            }
        };

        $result = $channel->send($notifiable, $notification);

        $this->assertIsArray($result);
        $this->assertEquals('whatsapp', $result['messaging_product']);
    }

    public function test_send_text_method(): void
    {
        $mockClient = $this->getMockBuilder(WhatsAppClient::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['send'])
            ->getMock();

        $mockClient->expects($this->once())
            ->method('send')
            ->with($this->callback(function ($payload) {
                return $payload['to'] === '94771234567'
                    && $payload['text']['body'] === 'Hello world';
            }))
            ->willReturn(['messaging_product' => 'whatsapp', 'messages' => [['id' => 'wamid.456']]]);

        $result = $mockClient->sendText('94771234567', 'Hello world');
        $this->assertIsArray($result);
    }
}

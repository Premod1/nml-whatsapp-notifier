<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use YourCompany\WhatsApp\WhatsAppClient;
use YourCompany\WhatsApp\Exceptions\WhatsAppApiException;

class TestWhatsAppCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:test {phone : Target phone number with country code (e.g. 94771234567)} {--type=template : Message type (template or text)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test WhatsApp message using Meta Cloud API';

    /**
     * Execute the console command.
     */
    public function handle(WhatsAppClient $wa)
    {
        $phone = $this->argument('phone');
        $type = $this->option('type');

        $this->info("Sending {$type} message to: {$phone}...");

        try {
            if ($type === 'text') {
                $response = $wa->sendText($phone, 'Hello from WhatsApp Notifier Laravel Package!');
            } else {
                $response = $wa->sendTemplate(
                    to: $phone,
                    templateName: 'hello_world',
                    languageCode: 'en_US'
                );
            }

            $this->info('Message sent successfully!');
            $this->line(json_encode($response, JSON_PRETTY_PRINT));
        } catch (WhatsAppApiException $e) {
            $this->error("Failed to send WhatsApp message: " . $e->getMessage());
        }
    }
}

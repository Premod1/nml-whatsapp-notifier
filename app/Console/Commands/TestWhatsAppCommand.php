<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Nml\WhatsApp\WhatsAppClient;
use Nml\WhatsApp\Exceptions\WhatsAppApiException;

class TestWhatsAppCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'whatsapp:test 
                            {phone : Target phone number with country code (e.g. 94771234567)} 
                            {--type=template : Message type (template or text)} 
                            {--template=hello_world : Template name} 
                            {--params= : Comma-separated body parameters} 
                            {--lang=en_US : Language code}';

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
                $templateName = $this->option('template');
                $lang = $this->option('lang');
                $rawParams = $this->option('params');

                $components = [];
                if ($rawParams !== null && $rawParams !== '') {
                    $paramList = array_map('trim', explode(',', $rawParams));
                    $components[] = [
                        'type' => 'body',
                        'parameters' => array_map(fn($p) => ['type' => 'text', 'text' => $p], $paramList),
                    ];
                }

                $response = $wa->sendTemplate(
                    to: $phone,
                    templateName: $templateName,
                    languageCode: $lang,
                    components: $components
                );
            }

            $this->info('Message sent successfully!');
            $this->line(json_encode($response, JSON_PRETTY_PRINT));
        } catch (WhatsAppApiException $e) {
            $this->error("Failed to send WhatsApp message: " . $e->getMessage());
        }
    }
}

<?php

namespace Nml\WhatsApp;

use Illuminate\Support\ServiceProvider;

class WhatsAppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/whatsapp.php', 'whatsapp');

        $this->app->singleton(WhatsAppClient::class, function ($app) {
            return new WhatsAppClient(config('whatsapp', []));
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/whatsapp.php' => config_path('whatsapp.php'),
            ], 'whatsapp-config');
        }
    }
}

<?php

namespace Nml\WhatsApp\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Nml\WhatsApp\WhatsAppServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            WhatsAppServiceProvider::class,
        ];
    }
}

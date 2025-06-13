<?php
declare(strict_types=1);

namespace PrismPHP\Runner;

use PrismPHP\Kernel;

class HttpRunner implements RunnerInterface
{
    public function run(): void 
    {
        $kernel = new Kernel($_ENV['APP_ENV'], $_ENV['APP_DEBUG']);
        $kernel->boot();
        $kernel->handle();
    }
}


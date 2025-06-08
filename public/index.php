<?php
declare(strict_types=1);

use PrismPHP\Exception\ConfigurationException;
use PrismPHP\Kernel;

require __DIR__.'/../vendor/autoload.php';

/*
 * TODOs (in priority order):
 *
 * TODO 1.  Add PHPDoc annotations
 * TODO 2.  Integrate logging
 * TODO 3.  Improve exception handling
 * TODO 4.  Integrate caching
 * TODO 5.  Create HTTP request/response abstractions
 * TODO 6.  Implement middleware pipeline (error handler, authentication, CSRF, body parsing…)
 * TODO 7.  Implement routing
 * TODO 8.  Integrate a template engine
 * TODO 9.  Write unit tests for core components
 * TODO 10. Write some basic bundles (ORM, Cryptography, …)
 * TODO 11. Write some components in C++
 * TODO 12. Write binaries (migrations, project scaffolding, …)
 */

try {
    $env = $_SERVER['APP_ENV'] ?? 'dev';
    $kernel = new Kernel($env);

    $kernel->boot();

} catch (ConfigurationException $e) {
    echo "ConfigurationException :\n" . $e->getMessage();
    exit(1);
} catch (\Throwable $e) {
    echo "Error :\n" . $e->getMessage();
    exit(1);
}

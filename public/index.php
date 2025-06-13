<?php
declare(strict_types=1);

require_once dirname(__DIR__) ."/vendor/autoload.php";

/*
 * TODOs (in priority order):
 *
 * TODO 1. Continuously add PHPDoc annotations
 * 2.  Integrate logging (initial logging setup done)
 * TODO 3.  Improve exception handling (make messages more clearer, remove all bad try/catch and related)
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

(new \PrismPHP\Runtime\PrismRuntime())->run();

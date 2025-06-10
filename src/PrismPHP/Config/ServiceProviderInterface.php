<?php
declare(strict_types=1);

namespace PrismPHP\Config;

interface ServiceProviderInterface
{
    public function register(): array;
}
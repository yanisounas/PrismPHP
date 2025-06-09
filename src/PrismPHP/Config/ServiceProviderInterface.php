<?php

namespace PrismPHP\Config;

interface ServiceProviderInterface
{
    public function register(): array;
}
<?php

namespace PrismPHP\Config;

interface ServiceLoaderInterface
{
    public function getRawDefinitions(): array;
    public function getParametersDefinitions(): array;
    public function getServicesDefinitions(): array;
}
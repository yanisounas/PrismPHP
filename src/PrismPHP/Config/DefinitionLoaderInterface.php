<?php

namespace PrismPHP\Config;

/**
 * Interface providing methods to load and retrieve various definitions used
 * in a Dependency Injection Container (DIC) context.
 */
interface DefinitionLoaderInterface
{
    public function getProviders(): array;
    public function getDefinitions(): array;
    public function getParameters(): array;
    public function getServices(): array;
    public function getDICSettings(): array;
}
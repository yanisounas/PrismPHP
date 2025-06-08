<?php

namespace PrismPHP\Config;

interface ServiceLoaderInterface
{
    /**
     * Retrieves the raw definitions as an array.
     *
     * @return array<string, mixed> The raw definitions.
     */
    public function getRawDefinitions(): array;

    /**
     * Retrieves the parameter definitions as an array.
     *
     * @return array<string, mixed> The parameter definitions.
     */
    public function getParametersDefinitions(): array;

    /**
     * Retrieves the service definitions as an array.
     *
     * @return array<string, mixed> The service definitions.
     */
    public function getServicesDefinitions(): array;
}
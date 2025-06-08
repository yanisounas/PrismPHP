<?php

namespace PrismPHP\DependencyInjection;

use PrismPHP\Exception\ParameterNotFoundException;

class ParameterBag implements ParameterBagInterface
{
    public function __construct(private array $_parameters = []) {}

    public function add(array $parameters): void
    {
        foreach ($parameters as $key => $value)
            $this->set($key, $value);
    }

    public function get(string $name, bool $nullOnMissing = false): mixed
    {
        if (!$this->has($name))
        {
            if ($nullOnMissing)
                return null;

            throw new ParameterNotFoundException(sprintf(
                "Parameter '%s' not found.",
                $name
                )
            );
        }

        return $this->_parameters[$name];
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->_parameters);
    }

    public function set(string $name, mixed $value): void
    {
        $this->_parameters[$name] = $value;
    }

    public function all(): array
    {
        return $this->_parameters;
    }
}
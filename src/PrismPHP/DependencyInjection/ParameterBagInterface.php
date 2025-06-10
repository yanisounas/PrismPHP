<?php
declare(strict_types=1);

namespace PrismPHP\DependencyInjection;

interface ParameterBagInterface
{
    public function get(string $name): mixed;
    public function has(string $name): bool;
    public function set(string $name, mixed $value): void;
    public function all(): array;
    public function add(array $parameters): void;
}
<?php
declare(strict_types=1);

namespace PrismPHP\Kernel;

interface KernelInterface
{
    public function boot(): void;
    public function handle(): void;
}

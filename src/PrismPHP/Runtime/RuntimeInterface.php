<?php
declare(strict_types=1);

namespace PrismPHP\Runtime;

use PrismPHP\Runner\RunnerInterface;

interface RuntimeInterface
{
    public function run(); 
    public function getRunner(): RunnerInterface;
}

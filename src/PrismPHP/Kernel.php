<?php
declare(strict_types=1);

namespace PrismPHP;

use PrismPHP\Config\ServiceLoader;
use PrismPHP\Config\DotenvLoader;
use PrismPHP\DependencyInjection\ContainerFactory;
use PrismPHP\Exception\ConfigurationException;
use PrismPHP\Utils\PathResolver;

class Kernel
{
    public function __construct(private string $_env, private string $_configDir = '')
    {
        $this->_configDir = rtrim(
            ($this->_configDir === '' ? PathResolver::getConfigDir() : $this->_configDir),
            '/'
        );
    }


    public function boot(): void
    {
        DotenvLoader::load(); 

        if (($_ENV['APP_ENV'] ?? '') !== $this->_env)
            throw new ConfigurationException(sprintf(
                "Environment mismatch. Expected `%s` but got `%s`.",
                $this->_env,
                $_ENV['APP_ENV'])
            );

        $container = ContainerFactory::createFromLoader((new ServiceLoader($this->_configDir, $this->_env)));
    }
}


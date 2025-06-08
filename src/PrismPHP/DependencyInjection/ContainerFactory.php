<?php

namespace PrismPHP\DependencyInjection;

use DI\ContainerBuilder;
use PrismPHP\Config\ServiceLoaderInterface;
use PrismPHP\Exception\ConfigurationException;
use PrismPHP\Utils\PathResolver;
use Psr\Container\ContainerInterface;

class ContainerFactory
{
    public static function createFromLoader(ServiceLoaderInterface $loader): ContainerInterface
    {
        return self::create(
            $loader->getServicesDefinitions(),
            $loader->getParametersDefinitions()
        );
    }

    public static function create(
        array $services,
        array $parameters,
    ): ContainerInterface
    {
        if (!isset($services['_defaults']) || !is_array($services['_defaults']))
            throw new ConfigurationException("Missing or invalid `_defaults` key in services.yaml.");

        $defaults= $services['_defaults'];
        unset($services['_defaults']);

        foreach (['autowire', 'attributes', 'compilation'] as $key)
            if (isset($defaults[$key]) && !is_bool($defaults[$key]))
                throw new ConfigurationException(sprintf(
                    '`_defaults.%s` must be a boolean in services.yaml, got `%s`.',
                    $key,
                    gettype($defaults[$key])
                ));

        $builder = new ContainerBuilder();

        $builder->useAutowiring((bool)($defaults['autowire'] ?? true));
        $builder->useAttributes((bool)($defaults['attributes'] ?? false));

        $builder->addDefinitions($parameters);
        $builder->addDefinitions($services);

        if (isset($defaults['compilation']) && $defaults['compilation'] === true)
        {
            $cacheDir = PathResolver::getCacheDir() . "/di";
            if (!is_dir($cacheDir) && !mkdir($cacheDir, 0777, true) && !is_dir($cacheDir))
                throw new ConfigurationException(sprintf(
                    'Unable to create DI cache directory "%s".',
                    $cacheDir
                ));
            $builder->enableCompilation($cacheDir);
        }

        return $builder->build();
    }
}
<?php

namespace PrismPHP\DependencyInjection;

use DI\ContainerBuilder;
use Exception;
use PrismPHP\Config\ServiceLoaderInterface;
use PrismPHP\Exception\ConfigurationException;
use PrismPHP\Utils\PathResolver;
use Psr\Container\ContainerInterface;
use function DI\autowire;
use function DI\get;

/**
 * Creates a dependency injection container
 */
class ContainerFactory
{

    /**
     * Creates a DI container from the given service loader.
     *
     * Delegates to {@see \PrismPHP\DependencyInjection\ContainerFactory::create()} for complete documentation
     *
     * @param ServiceLoaderInterface $loader The loader containing services and parameters.
     * @param array<string, mixed> $runtimeParameters
     * @return ContainerInterface
     *
     * @throws Exception
     * @see \PrismPHP\DependencyInjection\ContainerFactory::create()
     */
    public static function createFromLoader(ServiceLoaderInterface $loader, array $runtimeParameters = []): ContainerInterface
    {
        return self::create(
            $loader->getServicesDefinitions(),
            $loader->getParametersDefinitions(),
            $runtimeParameters
        );
    }

    /**
     * Creates a DI container with the given service and parameter definitions.
     *
     * @param array<string, mixed> $services           Array of service definitions to be injected into the container
     * @param array<string, mixed> $parameters         Array of parameter definitions to be injected into the container.
     * @param array<string, mixed> $runtimeParameters  Runtime-calculated parameters (key => value)
     * used to augment configuration loaded from the YAML files. Any parameter defined in YAML with the same key will
     * override the corresponding runtime parameter.
     *
     * @return ContainerInterface                   Returns the built container instance.
     * @throws ConfigurationException|Exception     If `_defaults` is missing or invalid, if `_defaults` contains
     * invalid types, or if the DI cache directory cannot be created.
     */
    public static function create(
        array $services,
        array $parameters,
        array $runtimeParameters = [],
    ): ContainerInterface
    {
        if (!isset($services['_defaults']) || !is_array($services['_defaults']))
            throw new ConfigurationException("Missing or invalid `_defaults` key in services.yaml.");

        $defaults= $services['_defaults'];
        unset($services['_defaults']);

        foreach (['autowire', 'attributes', 'compilation'] as $key)
        {
            if (!isset($defaults[$key])) continue;

            $normalized = filter_var($defaults[$key], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($normalized === null)
                throw new ConfigurationException(sprintf(
                    "`_defaults.%s` must be a boolean in services.yaml, got `%s`.",
                    $key,
                    is_scalar($defaults[$key]) ? var_export($defaults[$key], true) : gettype($defaults[$key])
                    )
                );

            $defaults[$key] = $normalized;
        }

        $parameters = array_merge($runtimeParameters, $parameters);

        $builder = new ContainerBuilder();

        $builder->useAutowiring(($defaults['autowire'] ?? true));
        $builder->useAttributes(($defaults['attributes'] ?? false));

        $builder->addDefinitions([
            ParameterBag::class => autowire()->constructor($parameters),
            ParameterBagInterface::class => get(ParameterBag::class),
        ]);

        $builder->addDefinitions($parameters);
        $builder->addDefinitions($services);

        if (isset($defaults['compilation']) && $defaults['compilation'] === true)
        {
            $cacheDir = PathResolver::getCacheDir() . "/di";
            if (!is_dir($cacheDir) && !mkdir($cacheDir, 0777, true) && !is_dir($cacheDir))
                throw new ConfigurationException(sprintf(
                    'Unable to create DI cache directory "%s".',
                    $cacheDir
                    )
                );
            $builder->enableCompilation($cacheDir);
        }

        return $builder->build();
    }
}
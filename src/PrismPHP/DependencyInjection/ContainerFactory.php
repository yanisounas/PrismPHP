<?php
declare(strict_types=1);

namespace PrismPHP\DependencyInjection;

use DI\ContainerBuilder;
use Exception;
use PrismPHP\Config\DefinitionLoaderInterface;
use PrismPHP\Config\Exception\ConfigurationException;
use PrismPHP\Utils\PathResolver;
use Psr\Container\ContainerInterface;
use function DI\autowire;
use function DI\factory;
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
     * @param DefinitionLoaderInterface $loader The loader containing services and parameters.
     * @param array<string, mixed> $runtimeParameters
     * @return ContainerInterface
     *
     * @throws Exception
     * @see \PrismPHP\DependencyInjection\ContainerFactory::create()
     */
    public static function createFromLoader(DefinitionLoaderInterface $loader, array $runtimeParameters = []): ContainerInterface
    {
        return self::create(
            $loader->getServices(),
            $loader->getParameters(),
            $loader->getDICSettings(),
            $loader->getProviders(),
            $runtimeParameters
        );
    }

    /**
     * Creates a DI container with the given service and parameter definitions.
     *
     * @param array<string, mixed> $services            Array of service definitions to be injected into the container
     * @param array<string, mixed> $parameters          Array of parameter definitions to be injected into the container.
     * @param array<string, bool|string> $DICSettings   Array of DI Container settings (autowire, attributes, compilation)
     * @param array<string, mixed> $runtimeParameters   Runtime-calculated parameters (key => value)
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
        array $DICSettings,
        array $providers,
        array $runtimeParameters = [],
    ): ContainerInterface
    {
        if (!empty($DICSettings))
        {
            foreach ($DICSettings as $key => $value)
            {
                if (!in_array($key, ['autowire', 'attributes', 'compilation']))
                    throw new ConfigurationException(sprintf(
                        "Unknown DI Container setting key `%s` in services.php", $key
                        )
                    );

                $normalized = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($normalized === null)
                    throw new ConfigurationException(sprintf(
                        "`%s` must be a boolean in services.php, got `%s`.",
                            $key,
                            is_scalar($value) ? var_export($value, true) : gettype($value),
                        )
                    );

                $DICSettings[$key] = $normalized;
            }
        }

        $parameters = array_merge($runtimeParameters, $parameters);

        $normalizedProviders = [];
        foreach ($providers as $providerClass)
        {
            if(!class_exists($providerClass))
                throw new ConfigurationException(sprintf(
                    "Provider `%s` does not exist", $providerClass
                    )
                );

            foreach ((new $providerClass())->register() as $id => $callable)
                $normalizedProviders[$id] = factory($callable);
        }

        $builder = new ContainerBuilder();

        $builder->useAutowiring(($DICSettings['autowire'] ?? true));
        $builder->useAttributes(($DICSettings['attributes'] ?? false));

        $builder->addDefinitions([
            ParameterBag::class => autowire()->constructor($parameters),
            ParameterBagInterface::class => get(ParameterBag::class),
        ]);

        $builder->addDefinitions(array_merge($parameters, $services, $normalizedProviders));

        if (isset($DICSettings['compilation']) && $DICSettings['compilation'] === true)
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
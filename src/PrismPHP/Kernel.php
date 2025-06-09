<?php
declare(strict_types=1);

namespace PrismPHP;

use Exception;
use PrismPHP\Config\DefinitionLoader;
use PrismPHP\Config\DotenvLoader;
use PrismPHP\Config\Exception\ConfigurationException;
use PrismPHP\DependencyInjection\ContainerFactory;
use PrismPHP\Utils\PathResolver;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * Represents the core application kernel responsible for initializing
 * the application environment and loading configurations.
 */
class Kernel
{
    private ContainerInterface $_container;
    /**
     * Constructor for initializing the environment and configuration directory.
     *
     * @param string $_configDir    The configuration directory path. Defaults to an empty string, which resolves to
     * the default configuration directory.
     *
     * @return void
     */
    public function __construct(private string $_configDir = '')
    {
        $this->_configDir = rtrim(
            ($this->_configDir === '' ? PathResolver::getConfigDir() : $this->_configDir),
            '/'
        );
    }


    /**
     * @return void
     *
     * @throws ConfigurationException|Exception If the environment specified in the configuration does not match
     * the actual application environment.
     */
    public function boot(): void
    {
        DotenvLoader::load();

        if (!isset($_ENV['APP_ENV']))
            throw new ConfigurationException("`APP_ENV` is not set in .env.");

        if (!isset($_ENV['APP_NAME']))
            throw new ConfigurationException("`APP_NAME` is not set in .env.");

        if (isset($_ENV['APP_DEBUG']) && !is_bool($_ENV['APP_DEBUG']))
        {
            $normalized = filter_var($_ENV['APP_DEBUG'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($normalized === null)
                throw new ConfigurationException(sprintf(
                        "`APP_DEBUG` must be a boolean, got %s.",
                        is_scalar($_ENV['APP_DEBUG']) ?
                            var_export($_ENV['APP_DEBUG'], true) :
                            gettype($_ENV['APP_DEBUG'])
                    )
                );

            $_ENV['APP_DEBUG'] = $normalized;
        }

        $env = $_ENV['APP_ENV'];

        $projectDir = PathResolver::getProjectDir();

        $runtimeParameters = [
            'kernel.environment' => $env,
            'kernel.debug'       => $_ENV['APP_DEBUG'],

            'kernel.project_dir' => $projectDir,
            'kernel.config_dir'  => $this->_configDir,
            'kernel.cache_dir'   => $projectDir . '/var/cache',
            'kernel.logs_dir'    => $projectDir . '/var/logs',
            'kernel.public_dir'  => $projectDir . '/public',
            'kernel.tmp_dir'     => $projectDir . '/var/tmp',

            'cache.default_ttl'  => 3600,
            'cache.adapter'      => 'filesystem',

            'app.name'           => ($_ENV['APP_NAME']),
            'app.secret'         => ($_ENV['APP_SECRET'] ?? null),
            'app.locale'         => ($_ENV['APP_LOCALE'] ?? null),
            'app.timezone'       => ($_ENV['APP_TIMEZONE'] ?? null),

            'database.url'       => $_ENV['DATABASE_URL'],

            'template.path'      =>  PathResolver::getProjectDir() .'/templates',
        ];


        $this->_container = ContainerFactory::createFromLoader((new DefinitionLoader($this->_configDir, $env)), $runtimeParameters);
    }

    public function getContainer(): ContainerInterface
    {
        return $this->_container;
    }
}
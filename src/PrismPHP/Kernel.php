<?php
declare(strict_types=1);

namespace PrismPHP;

use PrismPHP\Config\DefinitionLoader;
use PrismPHP\DependencyInjection\ContainerFactory;
use PrismPHP\Kernel\KernelInterface;
use PrismPHP\Utils\PathResolver;
use Psr\Container\ContainerInterface;

/**
 * Represents the core application kernel responsible for initializing
 * the application environment and loading configurations.
 */
class Kernel implements KernelInterface
{
    private ContainerInterface $_container;

    /**
     * Constructor for initializing the environment and configuration directory.
     *
     * @param string $_env
     * @param bool $_debug
     */
    public function __construct(private readonly string $_env, private readonly bool $_debug) {}


    public function handle(): void 
    {
        echo "handle";
    }


    public function boot(): void
    {
        $projectDir = PathResolver::getProjectDir();

        $runtimeParameters = [
            'kernel.environment' => $_ENV['APP_ENV'],
            'kernel.debug'       => $_ENV['APP_DEBUG'],

            'kernel.project_dir' => $projectDir,
            'kernel.config_dir'  => $projectDir . '/config',
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

            'template.path'      =>  $projectDir .'/templates',
        ];
        $this->_container = ContainerFactory::createFromLoader((new DefinitionLoader($projectDir . "/config", $this->_env)), $runtimeParameters);
    }

    public function getContainer(): ContainerInterface
    {
        return $this->_container;
    }
}

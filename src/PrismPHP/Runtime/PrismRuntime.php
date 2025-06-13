<?php
declare(strict_types=1);

namespace PrismPHP\Runtime;

use PrismPHP\Config\DotenvLoader;
use PrismPHP\Config\Exception\ConfigurationException;
use PrismPHP\DependencyInjection\ParameterBag;
use PrismPHP\ExceptionHandler\BootstrapExceptionHandler;
use PrismPHP\Logging\Provider\LoggerServiceProvider;
use PrismPHP\Runner\HttpRunner;
use PrismPHP\Runner\RunnerInterface;
use PrismPHP\Utils\PathResolver;
use Psr\Log\LoggerInterface;
use RuntimeException;

class PrismRuntime implements RuntimeInterface
{
    public function __construct() {}

    public function getRunner(): RunnerInterface 
    {
        if (in_array(PHP_SAPI, ['cli', 'phpdbg'], true))
            throw new RuntimeException("CLI mode is not yet implemented"); 
        return new HttpRunner();
    }

    public function run(): void
    {
        $this->_loadBootstrapServices();
        $this->_loadEnv();

        $runner = $this->getRunner();
        $runner->run();
    } 

    private function _loadEnv(): void
    {
        (new DotenvLoader())->load();

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
    }

    private function _loadBootstrapServices(): void
    {
        $logger = ((new LoggerServiceProvider())->register()[LoggerInterface::class])(new ParameterBag([
            'kernel.logs_dir' => PathResolver::getProjectDir() . "/var/logs",
            'app.name' => "bootstrap"
        ]));

        (new BootstrapExceptionHandler($logger))->register();
    }
}

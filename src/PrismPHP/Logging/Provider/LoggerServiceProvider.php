<?php
declare(strict_types=1);

namespace PrismPHP\Logging\Provider;

use Bramus\Monolog\Formatter\ColoredLineFormatter;
use Bramus\Monolog\Formatter\ColorSchemes\TrafficLight;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use PrismPHP\Config\Exception\ConfigurationException;
use PrismPHP\Config\ServiceProviderInterface;
use PrismPHP\DependencyInjection\ParameterBagInterface;
use Psr\Log\LoggerInterface;

class LoggerServiceProvider implements ServiceProviderInterface
{

    public function register(): array
    {
        return [LoggerInterface::class => function (ParameterBagInterface $bag) {
            $name = $bag->get("app.name");
            $logPath = $bag->get("kernel.logs_dir") . "/$name.log";
            $dir = dirname($logPath);

            $logger = new Logger($name);

            if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir))
                throw new ConfigurationException(sprintf(
                    "Can't create `%s`", $dir
                    )
                );

            $logger->pushHandler(new RotatingFileHandler($logPath, 7, Level::Debug));

            $consoleHandler = new StreamHandler("php://stdout", Level::Debug);
            $consoleHandler->setFormatter(new ColoredLineFormatter(
                new TrafficLight(),
                "[%datetime%] %level_name%: %message% %context%\n",
                "Y-m-d H:i:s",
                true,
                true
            ));

            $logger->pushHandler($consoleHandler);

            return $logger;
        }];
    }
}
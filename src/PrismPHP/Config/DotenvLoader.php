<?php
declare(strict_types=1);

namespace PrismPHP\Config;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use PrismPHP\Exception\ConfigurationException;
use PrismPHP\Utils\PathResolver;

class DotenvLoader
{
    public static function load(): void
    {
        $dir = PathResolver::getProjectDir();

        try
        {
            $dotenv = Dotenv::createImmutable($dir, '.env');
            $dotenv->load();

            $dotenv->required('APP_ENV');
            $envFile =  '.env.'. $_ENV['APP_ENV'];
            $path = $dir . '/' . $envFile;

            if (is_file($path))
                Dotenv::createMutable($dir, $envFile)->load();

        }catch (InvalidPathException $e)
        {
            throw new ConfigurationException(sprintf(
                ".env file not found in `%s`.", $dir
                )
            );
        } catch (\Throwable $e)
        {
            throw new ConfigurationException(sprintf(
                "Can't load environment variables : %s", $e->getMessage()
                )
            );
        }

    }
}

<?php
declare(strict_types=1);

namespace PrismPHP\Config;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use PrismPHP\Exception\ConfigurationException;
use PrismPHP\Utils\PathResolver;

/**
 * Load and manage environment variables.
 */
class DotenvLoader
{
    /**
     * Loads environment variables from the `.env` file and any environment-specific, if defined
     *
     * @throws ConfigurationException if the `.env` file cannot be found, required variables are missing,
     * or any error occurs during loading.
     */
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
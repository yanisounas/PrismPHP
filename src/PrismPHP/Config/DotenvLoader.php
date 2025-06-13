<?php
declare(strict_types=1);

namespace PrismPHP\Config;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use PrismPHP\Config\Exception\ConfigurationException;
use PrismPHP\Utils\PathResolver;

/**
 * Load and manage environment variables.
 */
class DotenvLoader
{
    public function __construct(private ?string $_envPath = null)
    {
       $this->_envPath ??= PathResolver::getProjectDir(); 
    }

    /**
     * Loads environment variables from the `.env` file and any environment-specific, if defined
     *
     * @throws ConfigurationException if the `.env` file cannot be found, required variables are missing,
     * or any error occurs during loading.
     */
    public function load(): void
    {
        try
        {
            $dotenv = Dotenv::createImmutable($this->_envPath, '.env');
            $dotenv->load();

            $dotenv->required('APP_ENV');
            $envFile =  '.env.'. $_ENV['APP_ENV'];
            $path = $this->_envPath. '/' . $envFile;

            if (is_file($path))
                Dotenv::createMutable($this->_envPath, $envFile)->load();

        }catch (InvalidPathException $e)
        {
            throw new ConfigurationException(sprintf(
                ".env file not found in `%s`.", $this->_envPath
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

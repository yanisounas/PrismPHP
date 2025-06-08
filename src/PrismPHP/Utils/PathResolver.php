<?php
declare(strict_types=1);

namespace PrismPHP\Utils;

/**
 * The PathResolver class provides utility methods to locate important directories.
 */
class PathResolver
{
    /**
     * Retrieves the root directory of the project by traversing upwards
     * from the current directory until the `composer.json` file is found.
     *
     * @return string The absolute path to the project's root directory.
     */
    public static function getProjectDir(): string
    {
        $dir = __DIR__;
        while (!is_file($dir . "/composer.json"))
            $dir = dirname($dir);   

        return $dir;
    }

    /**
     * Retrieves the directory path where configuration files are stored.
     *
     * @return string The path to the configuration directory.
     */
    public static function getConfigDir(): string
    {
        return self::getProjectDir() . "/config";    
    }

    /**
     * Retrieves the directory path where cache files are stored.
     *
     * @return string The path to the cache directory.
     */
    public static function getCacheDir(): string
    {
        return self::getProjectDir() . "/var/cache";
    }

    /**
     * Retrieves the directory path where log files are stored.
     *
     * @return string The path to the log directory.
     */
    public static function getLogDir(): string
    {
        return self::getProjectDir() . "/var/log";
    }
}

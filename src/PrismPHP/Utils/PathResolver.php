<?php
declare(strict_types=1);

namespace PrismPHP\Utils;


class PathResolver
{
    public static function getProjectDir(): string
    {
        $dir = __DIR__;
        while (!is_file($dir . "/composer.json"))
            $dir = dirname($dir);   

        return $dir;
    }

    public static function getConfigDir(): string
    {
        return self::getProjectDir() . "/config";    
    }

    public static function getCacheDir(): string
    {
        return self::getProjectDir() . "/var/cache";
    }
}

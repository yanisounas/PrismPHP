<?php
declare(strict_types=1);

namespace PrismPHP\Config;

use PrismPHP\Exception\ConfigurationException;
use Symfony\Component\Yaml\Yaml;

class ServiceLoader implements ServiceLoaderInterface
{
    private array $_raw;

    public function __construct(private string $_configDir, private string $_env) {}

    public function getRawDefinitions(): array
    {
        if (!isset($this->_raw))
            $this->loadRawDefinitions();

        return $this->_raw;
    }


    public function getServicesDefinitions(): array
    {
        return $this->getRawDefinitions()['services'];
    }

    public function getParametersDefinitions(): array
    {
        return $this->getRawDefinitions()['parameters'] ?? [];
    }

    private function loadRawDefinitions(): void
    {
        if (!is_file($this->_configDir . "/services.yaml"))
            throw new ConfigurationException(sprintf(
                "services.yaml is missing in %s", $this->_configDir
                )
            );

        $main = Yaml::parseFile($this->_configDir . "/services.yaml");

        if (!isset($main['services']) || !is_array($main['services']))
            throw new ConfigurationException("Missing or invalid `services` key in services.yaml.");

        $envFile = $this->_configDir . "/services." . $this->_env . ".yaml";
        if (is_file($envFile))
        {
            $envData = Yaml::parseFile($envFile);
            if (!isset($envData) || !is_array($envData['services']))
                throw new ConfigurationException(sprintf(
                    "Missing or invalid `services` key in %s.", $envFile
                    )
                );
            
            $main = array_replace_recursive($main, $envData);
            
        }

        $bundleDir = $this->_configDir . "/packages";

        if (is_dir($bundleDir))
        {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($bundleDir, \FilesystemIterator::SKIP_DOTS));

            foreach($iterator as $file)
            {
                if ($file->isFile() && $file->getExtension() === "yaml")
                {
                    $bundleData = Yaml::parseFile($file->getRealPath());
                    if (isset($bundleData) && is_array($bundleData))
                        $main = array_replace_recursive($main, $bundleData);
                }
            }
        }
        $this->_raw = $main;
    }
}

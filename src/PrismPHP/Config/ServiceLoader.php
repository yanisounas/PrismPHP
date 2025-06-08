<?php
declare(strict_types=1);

namespace PrismPHP\Config;

use PrismPHP\Exception\ConfigurationException;
use Symfony\Component\Yaml\Yaml;

/**
 * ServiceLoader class loads and manages service and parameter definitions from YAML configuration files.
 */
class ServiceLoader implements ServiceLoaderInterface
{
    private array $_raw;

    /**
     * Constructor method to initialize the configuration directory and environment.
     *
     * @param string $_configDir    The directory path for configuration files.
     * @param string $_env          The current environment (e.g., development, production).
     */
    public function __construct(private readonly string $_configDir, private readonly string $_env) {}

    /**
     * Retrieves the raw definitions. The methods call _loadRawDefinitions() if raw definitions are not loaded already.
     *
     * @return array<string, mixed> The raw definitions data.
     */
    public function getRawDefinitions(): array
    {
        if (!isset($this->_raw))
            $this->_loadRawDefinitions();

        return $this->_raw;
    }


    /**
     * Retrieves the service definitions from the raw definitions data.
     *
     * @return array<string, mixed> The services definitions data.
     */
    public function getServicesDefinitions(): array
    {
        return $this->getRawDefinitions()['services'];
    }

    /**
     * Retrieves the parameter definitions from the raw definitions.
     * If no parameter definitions are found, an empty array is returned.
     *
     * @return array<string,mixed> An associative array containing the parameter definitions.
     */
    public function getParametersDefinitions(): array
    {
        return $this->getRawDefinitions()['parameters'] ?? [];
    }

    /**
     * Loads and processes the raw service definitions from YAML configuration files.
     * This method reads the main `services.yaml` file, merges it with environment-specific
     * and package-specific configuration files if present, and stores the resulting data
     * in the `_raw` property. Environment-specific and package-specific configurations override
     * `services.yaml` definitions
     *
     * @return void
     *
     * @throws ConfigurationException If the `services.yaml` file is missing,
     *                                or required keys in the configuration files are invalid or absent.
     */
    private function _loadRawDefinitions(): void
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
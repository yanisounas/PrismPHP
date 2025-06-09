<?php

namespace PrismPHP\Config;

use PrismPHP\Config\Exception\ConfigurationException;

class DefinitionLoader implements DefinitionLoaderInterface
{
    private array $_definitions = [];

    public function __construct(private readonly string $_configDir, private readonly string $_env) {}

    public function getProviders(): array
    {
        return $this->getDefinitions()['providers'] ?? [];
    }

    /**
     * Retrieves the definitions. If definitions are not already loaded,
     * it triggers the loading process.
     *
     * @return array<string, mixed> The list of definitions.
     */
    public function getDefinitions(): array
    {
        if (!$this->_definitions)
            $this->_loadDefinitions();
        return $this->_definitions;
    }

    /**
     * Retrieves the parameters from the definitions.
     *
     * @return array<string, mixed> The array of parameters if defined, otherwise an empty array.
     */
    public function getParameters(): array
    {
        return $this->getDefinitions()['parameters'] ?? [];
    }

    /**
     * Retrieves the services from the definitions.
     *
     * @return array<string, mixed> The array of services if defined, otherwise an empty array.
     */
    public function getServices(): array
    {
        return $this->getDefinitions()['services'] ?? [];
    }

    /**
     * Retrieves the DIC (Dependency Injection Container) settings from the definitions.
     *
     * @return array<string, mixed> The array of DIC settings if defined, otherwise an empty array.
     */
    public function getDICSettings(): array
    {
        return $this->getDefinitions()['dic_settings'] ?? [];
    }

    /**
     * This method reads the primary `services.php` configuration file and optionally
     * merges environment-specific configurations, as well as additional bundle configurations
     * from a designated directory. It validates the existence and structure of these configurations
     * and updates the internal definitions accordingly.
     *
     * @return void
     *
     * @throws ConfigurationException If required configuration files are missing, invalid,
     *                                or contain improper definitions.
     */
    public function _loadDefinitions(): void
    {
        if(!is_file($this->_configDir . "/services.php"))
            throw new ConfigurationException(sprintf(
                "services.php is missing in %s", $this->_configDir
                )
            );

        $definitions = require_once $this->_configDir . "/services.php";

       if (!isset($definitions['services']) || !is_array($definitions['services']))
            throw new ConfigurationException("Missing or invalid `services` key in services.php.");

       $envFile = $this->_configDir . "/services." . $this->_env . ".php";
        if (is_file($envFile))
        {
            $envData = require_once $envFile;
            if (!isset($envData) || !is_array($envData['services']))
                throw new ConfigurationException(sprintf(
                    "Missing or invalid `services` key in %s.", $envFile
                    )
                );

            $definitions = array_replace_recursive($definitions, $envData);
        }

        $bundleDir = $this->_configDir . "/packages";
        if (is_dir($bundleDir))
        {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($bundleDir, \FilesystemIterator::SKIP_DOTS));

            foreach($iterator as $file)
            {
                if ($file->isFile() && $file->getExtension() === "php")
                {
                    $bundleData = require_once $file->getRealPath();
                    if (isset($bundleData['dic_settings']))
                        throw new ConfigurationException(sprintf(
                            "Bundle can't override dic_settings in %s",
                            $file->getRealPath()
                            )
                        );

                    if (isset($bundleData) && is_array($bundleData))
                        $definitions = array_replace_recursive($definitions, $bundleData);
                }
            }
        }
        $this->_definitions = $definitions;
    }
}
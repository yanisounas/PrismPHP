<?php

namespace PrismPHP\Tests\DependencyInjection;

use PrismPHP\Config\DefinitionLoaderInterface;
use PrismPHP\Config\Exception\ConfigurationException;
use PrismPHP\DependencyInjection\ContainerFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use function DI\autowire;

class ContainerFactoryTest extends TestCase
{
    private array $services;
    private array $parameters;
    private array $dicSettings;
    private array $providers;
    private array $runtimeParameters;

    protected function setUp(): void
    {
        $this->services = [
            'service.test' => autowire(\stdClass::class)
        ];

        $this->parameters = [
            'param.test' => 'value'
        ];

        $this->dicSettings = [
            'autowire' => true,
            'attributes' => false,
            'compilation' => false,
        ];

        $this->providers = [];

        $this->runtimeParameters = [
            'runtime.test' => 'runtime_value'
        ];
    }


    public function testCreateReturnsContainer(): void
    {
        $container = ContainerFactory::Create(
            $this->services,
            $this->parameters,
            $this->dicSettings,
            $this->providers,
            $this->runtimeParameters
        );

        $this->assertInstanceOf(ContainerInterface::class, $container);
        $this->assertSame("runtime_value", $container->get("runtime.test"));
        $this->assertSame("value", $container->get("param.test"));
        $this->assertInstanceOf(\stdClass::class, $container->get("service.test"));
    }

    public function testCreateFromLoaderUsesDefinitionLoader(): void
    {
        $loaderMock = $this->createMock(DefinitionLoaderInterface::class);

        $loaderMock->method("getServices")->willReturn($this->services);
        $loaderMock->method("getParameters")->willReturn($this->parameters);
        $loaderMock->method("getDICSettings")->willReturn($this->dicSettings);
        $loaderMock->method("getProviders")->willReturn($this->providers);

        $container = ContainerFactory::CreateFromLoader($loaderMock, $this->runtimeParameters);

        $this->assertInstanceOf(ContainerInterface::class, $container);
        $this->assertSame("runtime_value", $container->get("runtime.test"));
        $this->assertSame("value", $container->get("param.test"));
        $this->assertInstanceOf(\stdClass::class, $container->get("service.test"));
    }

    public function testInvalidDicSettingsKeyThrowsException(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Unknown DI Container setting key");

        ContainerFactory::Create(
            $this->services,
            $this->parameters,
            ['invalid_key' => true],
            $this->providers,
        );

    }

    public function testDicSettingsNonBooleanValueThrowsException(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("must be a boolean in services.php");

        ContainerFactory::Create(
            $this->services,
            $this->parameters,
            ['autowire' => 'invalid_value'],
            $this->providers,
        );
    }

    public function testNonExistentProviderThrowsException(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Provider `invalid_provider` does not exist");

        ContainerFactory::Create(
            $this->services,
            $this->parameters,
            $this->dicSettings,
            ['invalid_provider'],
        );
    }

    public function testCompilationEnablesCache(): void
    {
        $dicSettingsWithCompilation = $this->dicSettings;
        $dicSettingsWithCompilation['compilation'] = true;

        $cacheDir = \dirname(__DIR__, 2) . "/var/cache/di";

        if (is_dir($cacheDir))
            $this->deleteDirectory($cacheDir);

        $container = ContainerFactory::Create(
            $this->services,
            $this->parameters,
            $dicSettingsWithCompilation,
            $this->providers,
        );

        $this->assertInstanceOf(ContainerInterface::class, $container);
        $this->assertDirectoryExists($cacheDir);

        if (is_dir($cacheDir))
            $this->deleteDirectory($cacheDir);
    }

    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir))
            return;

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file)
            $file->isDir() && !$file->isLink() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());

        rmdir($dir);

    }
}

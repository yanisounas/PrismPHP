<?php
declare(strict_types=1);

namespace PrismPHP\Tests\Config;

use PrismPHP\Config\DefinitionLoader;
use PHPUnit\Framework\TestCase;
use PrismPHP\Config\Exception\ConfigurationException;

class DefinitionLoaderTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = __DIR__ . "/tmp_" . uniqid();
        mkdir($this->tmpDir, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->deleteDir($this->tmpDir);
    }

    public function deleteDir($dir): void
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

    public function testLoadsBaseConfiguration(): void
    {
        $content = <<<PHP
<?php
return [
    'services' => ['db' => 'MySQL'],
    'parameters' => ['env' => 'dev'],
    'providers' => ['ExempleProvider'],
    'dic_settings' => ['autowire' => true],
];
PHP;

        file_put_contents($this->tmpDir . "/services.php", $content);

        $loader = new DefinitionLoader($this->tmpDir, "test");
        $this->assertSame(['db' => 'MySQL'], $loader->getServices());
        $this->assertSame(['env' => 'dev'], $loader->getParameters());
        $this->assertSame(['ExempleProvider'], $loader->getProviders());
        $this->assertSame(['autowire' => true], $loader->getDICSettings());
    }

    public function testMergesEnvConfiguration(): void
    {
        file_put_contents($this->tmpDir . "/services.php", "<?php return['services' => ['foo' => 'bar']];");
        file_put_contents($this->tmpDir . "/services.test.php", "<?php return['services' => ['john' => 'doe']];");

        $loader = new DefinitionLoader($this->tmpDir, "test");
        $this->assertSame(['foo' => 'bar', 'john' => 'doe'], $loader->getServices());
    }

    public function testMergesPackages(): void
    {
        mkdir($this->tmpDir . "/packages", 0777, true);
        file_put_contents($this->tmpDir . "/services.php", "<?php return['services' => ['foo' => 'bar']];");
        file_put_contents($this->tmpDir . "/packages/bundle_test.php", "<?php return['services' => ['john' => 'doe']];");

        $loader = new DefinitionLoader($this->tmpDir, "test");
        $this->assertSame(['foo' => 'bar', 'john' => 'doe'], $loader->getServices());
    }

    public function testThrowsIfServicesPhpFileIsMissing(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("services.php is missing in " . $this->tmpDir);

        $loader = new DefinitionLoader($this->tmpDir, "test");
        $loader->getServices();
    }

    public function testThrowsIfServicesKeyIsMissing(): void
    {
        file_put_contents($this->tmpDir . "/services.php", "<?php return['dic_settings' => ['autowire' => true]];");

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Missing or invalid `services` key in services.php");

        $loader = new DefinitionLoader($this->tmpDir, "test");
        $loader->getServices();
    }

    public function testThrowsIfBundleOverridesDicSettings(): void
    {
        mkdir($this->tmpDir . "/packages", 0777, true);
        file_put_contents($this->tmpDir . "/services.php", "<?php return['services' => [],'dic_settings' => ['autowire' => true]];");
        file_put_contents($this->tmpDir . "/packages/bundle_test.php", "<?php return['dic_settings' => ['autowire' => false]];");

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Bundle can't override dic_settings");

        $loader = new DefinitionLoader($this->tmpDir, "test");
        $loader->getServices();
    }
}
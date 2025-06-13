<?php

namespace PrismPHP\Tests\Config;

use PrismPHP\Config\DotenvLoader;
use PHPUnit\Framework\TestCase;
use PrismPHP\Config\Exception\ConfigurationException;

class DotenvLoaderTest extends TestCase
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

    private function deleteDir(string $dir): void
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

    public function testLoadsBasicEnvFile(): void
    {
        file_put_contents($this->tmpDir . "/.env", "APP_ENV=test\nFOO=bar\n");

        $loader = new DotenvLoader($this->tmpDir);
        $loader->load();

        $this->assertSame("test", $_ENV['APP_ENV']);
        $this->assertSame("bar", $_ENV['FOO']);
        $this->assertSame("bar", $_SERVER['FOO']);
        $this->assertSame("bar", $_ENV['FOO']);
    }

    public function testLoadEnvFileWithOverride(): void
    {
        file_put_contents($this->tmpDir . "/.env", "APP_ENV=test\nFOO=bar\n");
        file_put_contents($this->tmpDir . "/.env.test", "FOO=override\nBAR=baz\n");

        $loader = new DotenvLoader($this->tmpDir);
        $loader->load();

        $this->assertSame("test", $_ENV['APP_ENV']);
        $this->assertSame("override", $_ENV['FOO']);
        $this->assertSame("baz", $_ENV['BAR']);
    }

    public function testThrowsIfEnvFileIsMissing(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage(".env file not found");

        $loader = new DotenvLoader("invalid_path");
        $loader->load();
    }

    public function testThrowsIfRequiredEnvVariableIsMissing(): void
    {
        file_put_contents($this->tmpDir . "/.env", "FOO=bar\n");

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage("Can't load environment variables");

        $loader = new DotenvLoader($this->tmpDir);
        $loader->load();
    }
}

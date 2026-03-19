<?php

declare(strict_types=1);

namespace UupCode\Cli\Tests\Unit;

use PHPUnit\Framework\TestCase;
use UupCode\Cli\PluginScaffold\Scaffolder;

final class WordPressDetectorTest extends TestCase
{
    private string $tmpDir;
    private Scaffolder $scaffolder;

    protected function setUp(): void
    {
        $this->tmpDir    = sys_get_temp_dir() . '/uupcode-wp-detect-' . uniqid();
        $this->scaffolder = new Scaffolder(__DIR__ . '/../../stubs');

        mkdir($this->tmpDir, 0755, true);
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->tmpDir);
    }

    public function testReturnsPluginsDirWhenWpLoadFoundInStartDir(): void
    {
        touch($this->tmpDir . '/wp-load.php');

        $result = $this->scaffolder->detectPluginsDir($this->tmpDir);

        $this->assertSame($this->tmpDir . '/wp-content/plugins', $result);
    }

    public function testReturnsPluginsDirWhenWpLoadFoundInParentDir(): void
    {
        // Simulate being inside wp-content/plugins
        $pluginsDir = $this->tmpDir . '/wp-content/plugins';
        mkdir($pluginsDir, 0755, true);
        touch($this->tmpDir . '/wp-load.php');

        $result = $this->scaffolder->detectPluginsDir($pluginsDir);

        $this->assertSame($this->tmpDir . '/wp-content/plugins', $result);
    }

    public function testReturnsPluginsDirWhenWpLoadFoundTwoLevelsUp(): void
    {
        // Simulate being inside an existing plugin directory
        $pluginDir = $this->tmpDir . '/wp-content/plugins/my-plugin';
        mkdir($pluginDir, 0755, true);
        touch($this->tmpDir . '/wp-load.php');

        $result = $this->scaffolder->detectPluginsDir($pluginDir);

        $this->assertSame($this->tmpDir . '/wp-content/plugins', $result);
    }

    public function testReturnsNullWhenNotInsideWordPressInstall(): void
    {
        // tmpDir has no wp-load.php anywhere in its tree
        $result = $this->scaffolder->detectPluginsDir($this->tmpDir);

        $this->assertNull($result);
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $file) {
            /** @var \SplFileInfo $file */
            $file->isDir() ? rmdir($file->getRealPath()) : unlink($file->getRealPath());
        }

        rmdir($dir);
    }
}

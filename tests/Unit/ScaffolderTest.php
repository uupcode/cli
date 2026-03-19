<?php

declare(strict_types=1);

namespace UupCode\Cli\Tests\Unit;

use PHPUnit\Framework\TestCase;
use UupCode\Cli\PluginScaffold\Scaffolder;

final class ScaffolderTest extends TestCase
{
    private string $outputDir;
    private Scaffolder $scaffolder;

    protected function setUp(): void
    {
        $this->outputDir  = sys_get_temp_dir() . '/uupcode-cli-test-' . uniqid();
        $this->scaffolder = new Scaffolder(__DIR__ . '/../../stubs');

        mkdir($this->outputDir, 0755, true);
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->outputDir);
    }

    public function testMainPluginFileIsCreatedWithSlugName(): void
    {
        $this->scaffolder->generate($this->inputs('My Test Plugin'), $this->outputDir);

        $this->assertFileExists($this->outputDir . '/my-test-plugin.php');
    }

    public function testTokensAreReplacedInFileContents(): void
    {
        $this->scaffolder->generate($this->inputs('My Test Plugin'), $this->outputDir);

        $content = (string) file_get_contents($this->outputDir . '/my-test-plugin.php');

        $this->assertStringContainsString('Plugin Name: My Test Plugin', $content);
        $this->assertStringContainsString('Text Domain: my-test-plugin', $content);
        $this->assertStringContainsString('MyTestPlugin\Plugin', $content);
        $this->assertStringNotContainsString('{{PluginName}}', $content);
        $this->assertStringNotContainsString('{{plugin-slug}}', $content);
        $this->assertStringNotContainsString('{{Namespace}}', $content);
    }

    public function testTokensAreReplacedInNestedFilePaths(): void
    {
        $this->scaffolder->generate($this->inputs('My Test Plugin'), $this->outputDir);

        // Namespace token in path: src/Providers/HookServiceProvider.php
        $this->assertFileExists($this->outputDir . '/src/Providers/HookServiceProvider.php');
    }

    public function testPluginNamePhpIsRenamedToSlug(): void
    {
        $this->scaffolder->generate($this->inputs('My Test Plugin'), $this->outputDir);

        $this->assertFileExists($this->outputDir . '/my-test-plugin.php');
        $this->assertFileDoesNotExist($this->outputDir . '/plugin-name.php');
    }

    public function testSrcDirectoryStructureIsCreated(): void
    {
        $this->scaffolder->generate($this->inputs('My Plugin'), $this->outputDir);

        $this->assertDirectoryExists($this->outputDir . '/src');
        $this->assertDirectoryExists($this->outputDir . '/src/Providers');
        $this->assertDirectoryExists($this->outputDir . '/src/Http');
        $this->assertDirectoryExists($this->outputDir . '/src/Models');
    }

    public function testResourcesDirectoryIsCreated(): void
    {
        $this->scaffolder->generate($this->inputs('My Plugin'), $this->outputDir);

        $this->assertDirectoryExists($this->outputDir . '/resources');
        $this->assertFileExists($this->outputDir . '/resources/index.js');
        $this->assertFileExists($this->outputDir . '/resources/admin.js');
    }

    public function testComposerJsonHasCorrectPackageName(): void
    {
        $this->scaffolder->generate([
            ...$this->inputs('My Plugin'),
            'vendor' => 'acme',
        ], $this->outputDir);

        $content = (string) file_get_contents($this->outputDir . '/composer.json');
        $this->assertStringContainsString('"acme/my-plugin"', $content);
        $this->assertStringNotContainsString('{{vendor}}', $content);
        $this->assertStringNotContainsString('{{plugin-slug}}', $content);
    }

    public function testProviderFilesContainCorrectNamespace(): void
    {
        $this->scaffolder->generate($this->inputs('My Test Plugin'), $this->outputDir);

        $content = (string) file_get_contents($this->outputDir . '/src/Providers/HookServiceProvider.php');

        $this->assertStringContainsString('namespace MyTestPlugin\\Providers;', $content);
        $this->assertStringNotContainsString('{{Namespace}}', $content);
    }

    public function testUnderscoreSlugIsReplacedInAjaxProvider(): void
    {
        $this->scaffolder->generate($this->inputs('My Test Plugin'), $this->outputDir);

        $content = (string) file_get_contents($this->outputDir . '/src/Providers/AjaxServiceProvider.php');

        $this->assertStringContainsString('my_test_plugin_example', $content);
        $this->assertStringNotContainsString('{{plugin_slug}}', $content);
    }

    /** @return array<string, string> */
    private function inputs(string $pluginName): array
    {
        return [
            'pluginName'  => $pluginName,
            'vendor'      => 'myvendor',
            'description' => 'A test plugin.',
            'authorName'  => 'Test Author',
            'authorUri'   => '',
            'pluginUri'   => '',
        ];
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

<?php

declare(strict_types=1);

namespace UupCode\Cli\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use UupCode\Cli\PluginScaffold\Scaffolder;

final class TokenDeriverTest extends TestCase
{
    private Scaffolder $scaffolder;

    protected function setUp(): void
    {
        $this->scaffolder = new Scaffolder(__DIR__ . '/../../stubs');
    }

    /** @return array<string, array{string, string, string, string}> */
    public static function pluginNameProvider(): array
    {
        return [
            'basic multi-word'      => ['My Awesome Plugin',      'my-awesome-plugin',      'my_awesome_plugin',      'MyAwesomePlugin'],
            'single word'           => ['Hello',                   'hello',                  'hello',                  'Hello'],
            'with numbers'          => ['Plugin 2024',             'plugin-2024',            'plugin_2024',            'Plugin2024'],
            'with special chars'    => ['Hello & World!',          'hello-world',            'hello_world',            'HelloWorld'],
            'extra spaces'          => ['My   Plugin',             'my-plugin',              'my_plugin',              'MyPlugin'],
            'mixed case'            => ['WooCommerce Integration', 'woocommerce-integration', 'woocommerce_integration', 'WooCommerceIntegration'],
            'leading/trailing junk' => ['--My Plugin--',           'my-plugin',              'my_plugin',              'MyPlugin'],
        ];
    }

    #[DataProvider('pluginNameProvider')]
    public function testSlugAndNamespaceDerivation(
        string $pluginName,
        string $expectedSlug,
        string $expectedSlugUnder,
        string $expectedNamespace,
    ): void {
        $tokens = $this->scaffolder->deriveTokens($this->inputs($pluginName));

        $this->assertSame($expectedSlug, $tokens['{{plugin-slug}}']);
        $this->assertSame($expectedSlugUnder, $tokens['{{plugin_slug}}']);
        $this->assertSame($expectedNamespace, $tokens['{{Namespace}}']);
    }

    public function testPluginNamePassedThrough(): void
    {
        $tokens = $this->scaffolder->deriveTokens($this->inputs('My Plugin'));

        $this->assertSame('My Plugin', $tokens['{{PluginName}}']);
    }

    public function testVendorIsSanitised(): void
    {
        $tokens = $this->scaffolder->deriveTokens([
            ...$this->inputs('My Plugin'),
            'vendor' => 'My-Vendor_01!',
        ]);

        $this->assertSame('myvendor01', $tokens['{{vendor}}']);
    }

    public function testAllExpectedTokenKeysArePresent(): void
    {
        $tokens = $this->scaffolder->deriveTokens($this->inputs('My Plugin'));

        $expected = [
            '{{PluginName}}',
            '{{plugin-slug}}',
            '{{plugin_slug}}',
            '{{Namespace}}',
            '{{vendor}}',
            '{{Description}}',
            '{{AuthorName}}',
            '{{AuthorUri}}',
            '{{PluginUri}}',
            '{{UupUtilitiesVersion}}',
        ];

        foreach ($expected as $key) {
            $this->assertArrayHasKey($key, $tokens, "Missing token: {$key}");
        }
    }

    public function testOptionalFieldsPassedThrough(): void
    {
        $tokens = $this->scaffolder->deriveTokens([
            ...$this->inputs('My Plugin'),
            'description' => 'A great plugin.',
            'authorName'  => 'Jane Doe',
            'authorUri'   => 'https://example.com',
            'pluginUri'   => 'https://example.com/plugin',
        ]);

        $this->assertSame('A great plugin.', $tokens['{{Description}}']);
        $this->assertSame('Jane Doe', $tokens['{{AuthorName}}']);
        $this->assertSame('https://example.com', $tokens['{{AuthorUri}}']);
        $this->assertSame('https://example.com/plugin', $tokens['{{PluginUri}}']);
    }

    /** @return array<string, string> */
    private function inputs(string $pluginName): array
    {
        return [
            'pluginName'  => $pluginName,
            'vendor'      => 'myvendor',
            'description' => 'A test plugin.',
            'authorName'  => '',
            'authorUri'   => '',
            'pluginUri'   => '',
        ];
    }
}

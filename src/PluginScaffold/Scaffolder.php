<?php

declare(strict_types=1);

namespace UupCode\Cli\PluginScaffold;

use WP_CLI;

class Scaffolder
{
    private const UUP_UTILITIES_VERSION = '^1.1';

    private string $stubsDir;

    public function __construct(string $stubsDir)
    {
        $this->stubsDir = rtrim($stubsDir, '/\\');
    }

    /** @param array<string, string> $assocArgs */
    public function run(array $assocArgs): void
    {
        $this->printBanner();

        $inputs    = $this->collectInputs($assocArgs);
        $tokens    = $this->deriveTokens($inputs);
        $cwd       = getcwd() ?: '.';
        $outputDir = isset($assocArgs['dir'])
            ? rtrim($assocArgs['dir'], '/\\')
            : ($this->detectPluginsDir($cwd) ?? $cwd) . '/' . $tokens['{{plugin-slug}}'];

        $this->printTokenSummary($tokens);
        WP_CLI::line('');
        WP_CLI::line("  Output : {$outputDir}");

        if (!$this->confirm('Generate plugin with these settings?')) {
            WP_CLI::line('Aborted.');
            return;
        }

        $created = $this->processStubs($tokens, $outputDir);
        foreach ($created as $file) {
            WP_CLI::line('  created  ' . $file);
        }

        $this->composerInstall($outputDir);
        $this->printSuccess($tokens, $outputDir);
    }

    /**
     * Generate plugin files without interactive prompts or CLI output.
     * Useful for programmatic use and testing.
     *
     * @param array<string, string> $inputs  Keys: pluginName, vendor, description, authorName, authorUri, pluginUri
     */
    public function generate(array $inputs, string $outputDir): void
    {
        $tokens = $this->deriveTokens($inputs);
        $this->processStubs($tokens, $outputDir);
    }

    // -------------------------------------------------------------------------
    // Input collection
    // -------------------------------------------------------------------------

    /**
     * @param  array<string, string> $assocArgs
     * @return array<string, string>
     */
    private function collectInputs(array $assocArgs): array
    {
        WP_CLI::line('');

        $pluginName  = $assocArgs['name']        ?? $this->prompt('Plugin Name', 'My Awesome Plugin');
        $vendor      = $assocArgs['vendor']      ?? $this->prompt('Vendor (Packagist prefix, lowercase)', 'myvendor');
        $description = $assocArgs['description'] ?? $this->prompt('Description', 'A WordPress plugin.');
        $authorName  = $assocArgs['author']      ?? $this->prompt('Author Name', '');
        $authorUri   = $assocArgs['author-uri']  ?? $this->prompt('Author URI (optional)', '');
        $pluginUri   = $assocArgs['plugin-uri']  ?? $this->prompt('Plugin URI (optional)', '');

        return compact('pluginName', 'vendor', 'description', 'authorName', 'authorUri', 'pluginUri');
    }

    private function prompt(string $question, string $default = ''): string
    {
        $hint = $default !== '' ? " [{$default}]" : '';
        fwrite(STDOUT, "  {$question}{$hint}: ");
        $value = trim((string) fgets(STDIN));
        return $value !== '' ? $value : $default;
    }

    private function confirm(string $question): bool
    {
        WP_CLI::line('');
        fwrite(STDOUT, "  {$question} [Y/n]: ");
        $answer = strtolower(trim((string) fgets(STDIN)));
        return $answer === '' || $answer === 'y' || $answer === 'yes';
    }

    // -------------------------------------------------------------------------
    // Token derivation
    // -------------------------------------------------------------------------

    /**
     * Derive template tokens from raw plugin inputs.
     *
     * @param  array<string, string> $inputs
     * @return array<string, string>
     */
    public function deriveTokens(array $inputs): array
    {
        $name = $inputs['pluginName'];

        $slug      = trim(strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name) ?? ''), '-');
        $slugUnder = str_replace('-', '_', $slug);
        $namespace = str_replace(' ', '', ucwords(preg_replace('/[^a-zA-Z0-9 ]+/', ' ', $name) ?? ''));

        return [
            '{{PluginName}}'          => $name,
            '{{plugin-slug}}'         => $slug,
            '{{plugin_slug}}'         => $slugUnder,
            '{{Namespace}}'           => $namespace,
            '{{vendor}}'              => strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $inputs['vendor']) ?? ''),
            '{{Description}}'         => $inputs['description'],
            '{{AuthorName}}'          => $inputs['authorName'],
            '{{AuthorUri}}'           => $inputs['authorUri'],
            '{{PluginUri}}'           => $inputs['pluginUri'],
            '{{UupUtilitiesVersion}}' => self::UUP_UTILITIES_VERSION,
        ];
    }

    // -------------------------------------------------------------------------
    // WordPress detection
    // -------------------------------------------------------------------------

    /**
     * Walk up the directory tree from $startDir looking for wp-load.php.
     * Returns the wp-content/plugins path if a WordPress root is found,
     * or null if the cwd is not inside a WordPress installation.
     */
    public function detectPluginsDir(string $startDir): ?string
    {
        $dir      = $startDir;
        $previous = null;

        while ($dir !== $previous) {
            if (file_exists($dir . '/wp-load.php')) {
                return $dir . '/wp-content/plugins';
            }
            $previous = $dir;
            $dir      = dirname($dir);
        }

        return null;
    }

    // -------------------------------------------------------------------------
    // Stub processing
    // -------------------------------------------------------------------------

    /**
     * @param  array<string, string> $tokens
     * @return list<string> Relative paths of created files.
     */
    private function processStubs(array $tokens, string $outputDir): array
    {
        if (!is_dir($this->stubsDir)) {
            WP_CLI::error("stubs/ directory not found at: {$this->stubsDir}");
            return [];
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->stubsDir, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        $created = [];

        foreach ($iterator as $item) {
            /** @var \SplFileInfo $item */
            $relative = substr($item->getPathname(), strlen($this->stubsDir) + 1);
            $dest     = $outputDir . '/' . $relative;

            if ($item->isDir()) {
                if (!is_dir($dest)) {
                    mkdir($dest, 0755, true);
                }
                continue;
            }

            $dest = str_replace(array_keys($tokens), array_values($tokens), $dest);
            $dest = str_replace('plugin-name.php', $tokens['{{plugin-slug}}'] . '.php', $dest);

            $destDir = dirname($dest);
            if (!is_dir($destDir)) {
                mkdir($destDir, 0755, true);
            }

            $this->replaceInFile($item->getPathname(), $dest, $tokens);
            $created[] = substr($dest, strlen($outputDir) + 1);
        }

        return $created;
    }

    /** @param array<string, string> $tokens */
    private function replaceInFile(string $source, string $dest, array $tokens): void
    {
        $content = file_get_contents($source);
        if ($content === false) {
            return;
        }
        $content = str_replace(array_keys($tokens), array_values($tokens), $content);
        file_put_contents($dest, $content);
    }

    // -------------------------------------------------------------------------
    // Composer install
    // -------------------------------------------------------------------------

    private function composerInstall(string $outputDir): void
    {
        WP_CLI::line('');
        WP_CLI::line('  Running composer install...');
        WP_CLI::line('');
        $cwd = (string) getcwd();
        chdir($outputDir);
        system('composer install --prefer-dist --no-interaction');
        chdir($cwd);
    }

    // -------------------------------------------------------------------------
    // Output helpers
    // -------------------------------------------------------------------------

    private function printBanner(): void
    {
        WP_CLI::line('');
        WP_CLI::line('  +---------------------------------------+');
        WP_CLI::line('  |   WordPress Plugin Scaffolder         |');
        WP_CLI::line('  |   powered by uup/utilities            |');
        WP_CLI::line('  +---------------------------------------+');
        WP_CLI::line('');
        WP_CLI::line('  Answer a few questions to generate your plugin.');
    }

    /** @param array<string, string> $tokens */
    private function printTokenSummary(array $tokens): void
    {
        WP_CLI::line('');
        WP_CLI::line('  ---- Plugin Summary ----');
        foreach ($tokens as $token => $value) {
            $label = trim($token, '{}');
            WP_CLI::line(sprintf('  %-24s %s', $label . ':', $value));
        }
    }

    /** @param array<string, string> $tokens */
    private function printSuccess(array $tokens, string $outputDir): void
    {
        $slug = $tokens['{{plugin-slug}}'];
        WP_CLI::line('');
        WP_CLI::success('Your plugin is ready.');
        WP_CLI::line('');
        WP_CLI::line('  Next steps:');
        WP_CLI::line('    1. Move ' . $outputDir . ' to your WordPress plugins directory');
        WP_CLI::line("    2. Activate '{$tokens['{{PluginName}}']}' in wp-admin");
        WP_CLI::line('    3. Open src/Plugin.php and start building');
        WP_CLI::line('');
        WP_CLI::line("  Main file : {$slug}.php");
        WP_CLI::line("  Namespace : {$tokens['{{Namespace}}']}\\ ");
        WP_CLI::line('');
    }
}

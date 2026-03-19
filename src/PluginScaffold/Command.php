<?php

declare(strict_types=1);

namespace UupCode\Cli\PluginScaffold;

class Command
{
    /**
     * Scaffold a new WordPress plugin pre-wired with uup/utilities.
     *
     * ## OPTIONS
     *
     * [--name=<name>]
     * : Plugin name.
     *
     * [--vendor=<vendor>]
     * : Packagist vendor prefix (lowercase).
     *
     * [--description=<desc>]
     * : Plugin description.
     *
     * [--author=<author>]
     * : Author name.
     *
     * [--author-uri=<uri>]
     * : Author URI.
     *
     * [--plugin-uri=<uri>]
     * : Plugin URI.
     *
     * [--dir=<dir>]
     * : Output directory. Defaults to <cwd>/<plugin-slug>.
     *
     * ## EXAMPLES
     *
     *     wp uup-plugin scaffold
     *     wp uup-plugin scaffold --name="My Plugin" --vendor=myvendor
     *     wp uup-plugin scaffold --name="My Plugin" --vendor=myvendor --dir=/path/to/output
     *
     * @when before_wp_load
     *
     * @param array<int, string>    $args
     * @param array<string, string> $assoc_args
     */
    public function __invoke(array $args, array $assoc_args): void
    {
        $scaffolder = new Scaffolder(__DIR__ . '/../../stubs');
        $scaffolder->run($assoc_args);
    }
}

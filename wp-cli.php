<?php
if (!defined('WP_CLI')) return;

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

WP_CLI::add_command('uup-plugin scaffold', UupCode\Cli\PluginScaffold\Command::class);
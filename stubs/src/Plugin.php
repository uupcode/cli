<?php
declare(strict_types=1);

namespace {{Namespace}};

use UupCode\Utilities\Plugin as BasePlugin;
use {{Namespace}}\Providers\HookServiceProvider;
use {{Namespace}}\Providers\RestServiceProvider;
use {{Namespace}}\Providers\AssetServiceProvider;
use {{Namespace}}\Providers\AdminServiceProvider;
use {{Namespace}}\Providers\BlockServiceProvider;
// Optional providers — uncomment as needed:
// use {{Namespace}}\Providers\AjaxServiceProvider;
// use {{Namespace}}\Providers\PostTypeServiceProvider;
// use {{Namespace}}\Providers\CronServiceProvider;
// use {{Namespace}}\Providers\ShortcodeServiceProvider;

final class Plugin
{
    private const MIN_PHP = '8.1';
    private const MIN_WP  = '6.0';

    /**
     * Plugins that must be active before this plugin loads.
     * Check by class or function existence — independent of install path.
     *
     * Example:
     *   'WooCommerce'            => ['class'    => 'WooCommerce'],
     *   'Advanced Custom Fields' => ['function' => 'acf'],
     *
     * @var array<string, array{class?: string, function?: string}>
     */
    private static array $requires = [];

    private function __construct() {}

    public static function boot(string $file): void
    {
        BasePlugin::boot($file);

        if (!self::requirementsMet()) {
            add_action('admin_notices', [static::class, 'requirementsNotice']);
            return;
        }

        // Activation/deactivation/uninstall hooks must be registered immediately —
        // they fire before plugins_loaded during activation requests.
        BasePlugin::onActivate([static::class, 'activate']);
        BasePlugin::onDeactivate([static::class, 'deactivate']);
        BasePlugin::onUninstall([static::class, 'uninstall']);

        // Defer everything else to plugins_loaded so all plugin classes
        // and functions are available for dependency checks.
        add_action('plugins_loaded', [static::class, 'init']);
    }

    public static function init(): void
    {
        load_plugin_textdomain('{{plugin-slug}}', false, dirname(BasePlugin::basename()) . '/languages');

        if (!self::dependenciesMet()) {
            add_action('admin_notices', [static::class, 'missingDependenciesNotice']);
            return;
        }

        (new HookServiceProvider())->register();
        (new RestServiceProvider())->register();
        (new AssetServiceProvider())->register();
        (new AdminServiceProvider())->register();
        (new BlockServiceProvider())->register();

        // Optional — uncomment as needed:
        // (new AjaxServiceProvider())->register();
        // (new PostTypeServiceProvider())->register();
        // (new CronServiceProvider())->register();
        // (new ShortcodeServiceProvider())->register();
    }

    public static function activate(): void
    {
        // TODO: run on plugin activation (e.g. create tables, set default options)
        flush_rewrite_rules();
    }

    public static function deactivate(): void
    {
        // TODO: run on plugin deactivation
        flush_rewrite_rules();
    }

    public static function uninstall(): void
    {
        // TODO: remove all plugin data (options, custom tables, etc.)
        // This runs when the plugin is deleted from wp-admin.
    }

    public static function requirementsNotice(): void
    {
        global $wp_version;

        if (version_compare(PHP_VERSION, self::MIN_PHP, '<')) {
            printf(
                '<div class="notice notice-error"><p><strong>{{PluginName}}</strong> requires PHP %s or higher. You are running PHP %s.</p></div>',
                self::MIN_PHP,
                PHP_VERSION
            );
        }

        if (version_compare($wp_version, self::MIN_WP, '<')) {
            printf(
                '<div class="notice notice-error"><p><strong>{{PluginName}}</strong> requires WordPress %s or higher. You are running WordPress %s.</p></div>',
                self::MIN_WP,
                esc_html($wp_version)
            );
        }
    }

    public static function missingDependenciesNotice(): void
    {
        $missing = array_filter(self::$requires, fn($check) => !self::isAvailable($check));

        foreach ($missing as $name => $check) {
            printf(
                '<div class="notice notice-error"><p><strong>{{PluginName}}</strong> requires <strong>%s</strong> to be installed and active.</p></div>',
                esc_html($name)
            );
        }
    }

    private static function requirementsMet(): bool
    {
        global $wp_version;

        return version_compare(PHP_VERSION, self::MIN_PHP, '>=')
            && version_compare($wp_version, self::MIN_WP, '>=');
    }

    private static function dependenciesMet(): bool
    {
        foreach (self::$requires as $check) {
            if (!self::isAvailable($check)) {
                return false;
            }
        }
        return true;
    }

    private static function isAvailable(array $check): bool
    {
        if (isset($check['class'])) {
            return class_exists($check['class']);
        }
        if (isset($check['function'])) {
            return function_exists($check['function']);
        }
        return true;
    }
}
<?php
declare(strict_types=1);

namespace {{Namespace}}\Providers;

use UupCode\Utilities\ServiceProvider;
use UupCode\Utilities\Attributes\Action;
use UupCode\Utilities\Attributes\Filter;

final class AdminServiceProvider extends ServiceProvider
{
    #[Action('admin_menu')]
    public function addMenuPages(): void
    {
        add_menu_page(
            '{{PluginName}}',
            '{{PluginName}}',
            'manage_options',
            '{{plugin-slug}}',
            [$this, 'renderPage'],
            'dashicons-admin-generic',
            90
        );
    }

    public function renderPage(): void
    {
        echo '<div class="wrap"><h1>{{PluginName}}</h1></div>';
    }

    #[Filter('plugin_action_links_{{plugin-slug}}/{{plugin-slug}}.php')]
    public function addActionLinks(array $links): array
    {
        $links[] = sprintf(
            '<a href="%s">%s</a>',
            admin_url('admin.php?page={{plugin-slug}}'),
            __('Settings', '{{plugin-slug}}')
        );
        return $links;
    }
}
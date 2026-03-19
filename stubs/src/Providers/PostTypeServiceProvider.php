<?php
declare(strict_types=1);

namespace {{Namespace}}\Providers;

use UupCode\Utilities\ServiceProvider;
use UupCode\Utilities\PostTypes\PostType;
use UupCode\Utilities\PostTypes\Taxonomy;

final class PostTypeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        PostType::make('{{plugin_slug}}_example', singular: 'Example', plural: 'Examples')
            ->supports('title', 'editor', 'thumbnail')
            ->public()
            ->showInRest()
            ->register();

        Taxonomy::make('{{plugin_slug}}_category', postTypes: '{{plugin_slug}}_example', singular: 'Category', plural: 'Categories')
            ->hierarchical()
            ->showAdminColumn()
            ->register();
    }
}
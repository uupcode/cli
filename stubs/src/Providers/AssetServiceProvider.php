<?php
declare(strict_types=1);

namespace {{Namespace}}\Providers;

use UupCode\Utilities\ServiceProvider;
use UupCode\Utilities\Attributes\Action;
use UupCode\Utilities\Assets\Asset;
use UupCode\Utilities\Plugin as BasePlugin;

final class AssetServiceProvider extends ServiceProvider
{
    #[Action('wp_enqueue_scripts')]
    public function enqueueFrontend(): void
    {
        $asset = $this->assetManifest('index');

        Asset::script('{{plugin-slug}}', BasePlugin::url('build/index.js'))
            ->deps(...$asset['dependencies'])
            ->version($asset['version'])
            ->footer()
            ->enqueue();

        Asset::style('{{plugin-slug}}', BasePlugin::url('build/index.css'))
            ->version($asset['version'])
            ->enqueue();
    }

    #[Action('admin_enqueue_scripts')]
    public function enqueueAdmin(): void
    {
        $asset = $this->assetManifest('admin');

        Asset::script('{{plugin-slug}}-admin', BasePlugin::url('build/admin.js'))
            ->deps(...$asset['dependencies'])
            ->version($asset['version'])
            ->footer()
            ->enqueue();

        Asset::style('{{plugin-slug}}-admin', BasePlugin::url('build/admin.css'))
            ->version($asset['version'])
            ->enqueue();
    }

    private function assetManifest(string $entry): array
    {
        $file = BasePlugin::path("build/{$entry}.asset.php");
        return file_exists($file) ? require $file : ['dependencies' => [], 'version' => '1.0.0'];
    }
}
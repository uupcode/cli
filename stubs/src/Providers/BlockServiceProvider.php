<?php
declare(strict_types=1);

namespace {{Namespace}}\Providers;

use UupCode\Utilities\ServiceProvider;
use UupCode\Utilities\Attributes\Action;
use UupCode\Utilities\Plugin as BasePlugin;

final class BlockServiceProvider extends ServiceProvider
{
    #[Action('init')]
    public function registerBlocks(): void
    {
        $blocksDir = BasePlugin::path('build/blocks');

        if (!is_dir($blocksDir)) {
            return;
        }

        foreach (new \DirectoryIterator($blocksDir) as $item) {
            if ($item->isDir() && !$item->isDot()) {
                register_block_type($item->getPathname());
            }
        }
    }
}
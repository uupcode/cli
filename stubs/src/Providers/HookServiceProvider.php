<?php
declare(strict_types=1);

namespace {{Namespace}}\Providers;

use UupCode\Utilities\ServiceProvider;
use UupCode\Utilities\Attributes\Action;

final class HookServiceProvider extends ServiceProvider
{
    #[Action('init')]
    public function onInit(): void
    {
        // Plugin initialisation logic goes here.
    }
}
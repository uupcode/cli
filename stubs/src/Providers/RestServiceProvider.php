<?php
declare(strict_types=1);

namespace {{Namespace}}\Providers;

use UupCode\Utilities\ServiceProvider;
use UupCode\Utilities\Attributes\Action;
use UupCode\Utilities\Http\Rest;
use {{Namespace}}\Http\Controllers\ExampleController;

final class RestServiceProvider extends ServiceProvider
{
    #[Action('rest_api_init')]
    public function registerRoutes(): void
    {
        Rest::namespace('{{plugin-slug}}/v1')
            ->get('/example', [ExampleController::class, 'index'])
            ->register();
    }
}
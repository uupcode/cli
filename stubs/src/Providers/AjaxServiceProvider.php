<?php
declare(strict_types=1);

namespace {{Namespace}}\Providers;

use UupCode\Utilities\ServiceProvider;
use UupCode\Utilities\Http\Ajax;
use {{Namespace}}\Http\Requests\ExampleRequest;

final class AjaxServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Nonce + authorization are handled by ExampleRequest.
        Ajax::handle('{{plugin_slug}}_example', function(ExampleRequest $request) {
            $name = $request->string('name');
            Ajax::json(['message' => "Hello, {$name}!"]);
        })->register();

        // Public (unauthenticated) handler — no request class needed:
        // Ajax::handle('{{plugin_slug}}_public', function(AjaxRequest $request) {
        //     Ajax::json(['ok' => true]);
        // })->public();
    }
}
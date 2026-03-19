<?php
declare(strict_types=1);

namespace {{Namespace}}\Http\Controllers;

use WP_REST_Request;
use WP_REST_Response;

final class ExampleController
{
    public function index(WP_REST_Request $request): WP_REST_Response
    {
        return new WP_REST_Response([
            'plugin'  => '{{PluginName}}',
            'version' => '1.0.0',
            'status'  => 'ok',
        ], 200);
    }
}

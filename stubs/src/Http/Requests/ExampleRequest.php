<?php
declare(strict_types=1);

namespace {{Namespace}}\Http\Requests;

use UupCode\Utilities\Http\AjaxRequest;

final class ExampleRequest extends AjaxRequest
{
    public function authorize(): bool
    {
        return is_user_logged_in();
    }

    public function nonceAction(): string
    {
        return '{{plugin_slug}}_example';
    }
}
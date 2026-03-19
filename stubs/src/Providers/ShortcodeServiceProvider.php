<?php
declare(strict_types=1);

namespace {{Namespace}}\Providers;

use UupCode\Utilities\ServiceProvider;
use UupCode\Utilities\Attributes\Action;
use UupCode\Utilities\Shortcode;

final class ShortcodeServiceProvider extends ServiceProvider
{
    #[Action('init')]
    public function registerShortcodes(): void
    {
        Shortcode::register('{{plugin-slug}}', [$this, 'render']);
    }

    public function render(array $atts, string $content = ''): string
    {
        $atts = shortcode_atts(['class' => ''], $atts, '{{plugin-slug}}');

        return sprintf(
            '<div class="{{plugin-slug}} %s">%s</div>',
            esc_attr($atts['class']),
            do_shortcode($content)
        );
    }
}
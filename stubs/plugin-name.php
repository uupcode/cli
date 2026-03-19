<?php
/**
 * Plugin Name: {{PluginName}}
 * Plugin URI:  {{PluginUri}}
 * Description: {{Description}}
 * Version:     1.0.0
 * Author:      {{AuthorName}}
 * Author URI:  {{AuthorUri}}
 * License:     GPL-2.0-or-later
 * Text Domain: {{plugin-slug}}
 * Domain Path: /languages
 * Requires PHP: 8.1
 */

declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use {{Namespace}}\Plugin;

require_once __DIR__ . '/vendor/autoload.php';

Plugin::boot(__FILE__);

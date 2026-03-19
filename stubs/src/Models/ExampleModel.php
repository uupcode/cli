<?php
declare(strict_types=1);

namespace {{Namespace}}\Models;

use UupCode\Utilities\Database\Model;

final class ExampleModel extends Model
{
    protected static string $table = '{{plugin_slug}}_examples';

    protected static array $casts = [
        // 'meta' => 'array',
    ];

    public static function createTable(): void
    {
        global $wpdb;

        $charset = $wpdb->get_charset_collate();
        $table   = self::table();

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
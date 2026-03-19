<?php

declare(strict_types=1);

// Stub WP_CLI before the autoloader so the real wp-cli runtime is never
// initialised during unit tests. PHP won't autoload a class that is already
// defined, so this takes precedence over the vendor class map.
if (!class_exists('WP_CLI')) {
    class WP_CLI
    {
        public static function line(string $message = ''): void
        {
        }

        public static function success(string $message): void
        {
        }

        /**
         * @param string|bool $exit
         */
        public static function error(string $message, $exit = true): void
        {
            if ($exit === true) {
                throw new \RuntimeException('WP_CLI::error — ' . $message);
            }
        }
    }
}

require_once dirname(__DIR__) . '/vendor/autoload.php';

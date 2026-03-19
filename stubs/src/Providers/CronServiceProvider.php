<?php
declare(strict_types=1);

namespace {{Namespace}}\Providers;

use UupCode\Utilities\ServiceProvider;
use UupCode\Utilities\Cron;

final class CronServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register a custom interval if needed:
        // Cron::addInterval('every_5_minutes', 300, 'Every 5 Minutes');

        Cron::add('{{plugin_slug}}_daily_sync', 'daily', [$this, 'dailySync']);
    }

    public function dailySync(): void
    {
        // TODO: scheduled task logic
    }
}
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Only initialize broadcasting if not running in console during package discovery
        if (!$this->app->runningInConsole() || !$this->isPackageDiscovery()) {
            Broadcast::routes();
            require base_path('routes/channels.php');
        }
    }

    /**
     * Check if we're running package discovery
     */
    private function isPackageDiscovery(): bool
    {
        return in_array('package:discover', $_SERVER['argv'] ?? []);
    }
}
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $modules = config('modules.modules', []);

        foreach ($modules as $name => $config) {
            if (!($config['enabled'] ?? false)) {
                continue;
            }

            if (isset($config['provider']) && class_exists($config['provider'])) {
                $this->app->register($config['provider']);
            }
        }
    }

    public function boot(): void
    {
        //
    }
}

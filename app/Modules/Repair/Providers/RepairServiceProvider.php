<?php

namespace App\Modules\Repair\Providers;

use App\Modules\Repair\Repositories\Contracts\DamageReportRepositoryInterface;
use App\Modules\Repair\Repositories\Contracts\MachineRepositoryInterface;
use App\Modules\Repair\Repositories\DamageReportRepository;
use App\Modules\Repair\Repositories\MachineRepository;
use App\Modules\Repair\Services\Contracts\DamageReportServiceInterface;
use App\Modules\Repair\Services\DamageReportService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RepairServiceProvider extends ServiceProvider
{
    protected string $moduleName = 'repair';
    protected string $moduleNamespace = 'App\Modules\Repair';

    public function register(): void
    {
        $this->registerRepositories();
        $this->registerServices();
    }

    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerViews();
        $this->registerCommands();
        $this->registerSchedule();
    }

    protected function registerRepositories(): void
    {
        $this->app->bind(
            DamageReportRepositoryInterface::class,
            DamageReportRepository::class
        );

        $this->app->bind(
            MachineRepositoryInterface::class,
            MachineRepository::class
        );
    }

    protected function registerServices(): void
    {
        $this->app->bind(
            DamageReportServiceInterface::class,
            DamageReportService::class
        );
    }

    protected function registerRoutes(): void
    {
        Route::middleware(['web', 'auth', 'module:repair'])
            ->prefix('repair')
            ->name('repair.')
            ->group(__DIR__ . '/../Routes/web.php');
    }

    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'repair');
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Modules\Repair\Console\Commands\SendDeadlineReminders::class,
            ]);
        }
    }

    protected function registerSchedule(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('repair:deadline-reminders')->dailyAt('08:00');
        });
    }
}

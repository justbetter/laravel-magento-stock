<?php

namespace JustBetter\MagentoStock;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use JustBetter\MagentoStock\Actions\DetermineStockModified;
use JustBetter\MagentoStock\Actions\MonitorWaitTimes;
use JustBetter\MagentoStock\Actions\ProcessStock;
use JustBetter\MagentoStock\Actions\ResolveStockCalculator;
use JustBetter\MagentoStock\Actions\SyncStock;
use JustBetter\MagentoStock\Actions\UpdateBackorders;
use JustBetter\MagentoStock\Actions\UpdateMsiStock;
use JustBetter\MagentoStock\Actions\UpdateSimpleStock;
use JustBetter\MagentoStock\Commands\CompareStockCommand;
use JustBetter\MagentoStock\Commands\MonitorWaitTimesCommand;
use JustBetter\MagentoStock\Commands\RetrieveAllStockCommand;
use JustBetter\MagentoStock\Commands\RetrieveStockCommand;
use JustBetter\MagentoStock\Commands\RetrieveUpdatedStockCommand;
use JustBetter\MagentoStock\Commands\SyncStockCommand;
use JustBetter\MagentoStock\Commands\UpdateStockCommand;
use JustBetter\MagentoStock\Events\StockChanged;
use JustBetter\MagentoStock\Listeners\SetStockRetrieveListener;

class ServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/magento-stock.php', 'magento-stock');

        $this->registerActions();
    }

    protected function registerActions(): static
    {
        ProcessStock::bind();
        DetermineStockModified::bind();
        ResolveStockCalculator::bind();

        UpdateBackorders::bind();

        SyncStock::bind();

        MonitorWaitTimes::bind();

        return $this;
    }

    public function boot(): void
    {
        $this
            ->bootMigrations()
            ->bootConfig()
            ->bootCommands()
            ->bootEvents();

        if (config('magento-stock.msi')) {
            UpdateMsiStock::bind();
        } else {
            UpdateSimpleStock::bind();
        }
    }

    protected function bootConfig(): static
    {
        $this->publishes([
            __DIR__.'/../config/magento-stock.php' => config_path('magento-stock.php'),
        ], 'config');

        return $this;
    }

    protected function bootCommands(): static
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                RetrieveStockCommand::class,
                RetrieveUpdatedStockCommand::class,
                RetrieveAllStockCommand::class,

                UpdateStockCommand::class,

                SyncStockCommand::class,

                CompareStockCommand::class,
                MonitorWaitTimesCommand::class,
            ]);
        }

        return $this;
    }

    protected function bootMigrations(): static
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        return $this;
    }

    protected function bootEvents(): static
    {
        Event::listen(StockChanged::class, SetStockRetrieveListener::class);

        return $this;
    }
}

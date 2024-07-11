<?php

namespace JustBetter\MagentoStock;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use JustBetter\MagentoStock\Actions\DetermineStockModified;
use JustBetter\MagentoStock\Actions\ProcessStocks;
use JustBetter\MagentoStock\Actions\ResolveStockCalculator;
use JustBetter\MagentoStock\Actions\Retrieval\RetrieveStock;
use JustBetter\MagentoStock\Actions\Retrieval\SaveStock;
use JustBetter\MagentoStock\Actions\Update\Sync\UpdateBackorders;
use JustBetter\MagentoStock\Commands\Comparinson\CompareStockCommand;
use JustBetter\MagentoStock\Commands\ProcessStocksCommand;
use JustBetter\MagentoStock\Commands\Retrieval\RetrieveAllStockCommand;
use JustBetter\MagentoStock\Commands\Retrieval\RetrieveStockCommand;
use JustBetter\MagentoStock\Commands\RetrieveUpdatedStockCommand;
use JustBetter\MagentoStock\Commands\Update\UpdateStockCommand;

class ServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/magento-stock.php', 'magento-stock');

        $this->registerActions();
    }

    protected function registerActions(): static
    {
        RetrieveStock::bind();
        SaveStock::bind();
        DetermineStockModified::bind();
        ResolveStockCalculator::bind();

        UpdateBackorders::bind();

        ProcessStocks::bind();

        return $this;
    }

    public function boot(): void
    {
        $this
            ->bootMigrations()
            ->bootConfig()
            ->bootCommands();
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
                RetrieveAllStockCommand::class,

                UpdateStockCommand::class,

                ProcessStocksCommand::class,

                CompareStockCommand::class,
            ]);
        }

        return $this;
    }

    protected function bootMigrations(): static
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        return $this;
    }
}

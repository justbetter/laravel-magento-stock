<?php

namespace JustBetter\MagentoStock;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use JustBetter\MagentoAsync\Events\BulkOperationStatusEvent;
use JustBetter\MagentoStock\Actions\Comparison\CompareMsiStock;
use JustBetter\MagentoStock\Actions\Comparison\CompareSimpleStock;
use JustBetter\MagentoStock\Actions\Comparison\CompareStock;
use JustBetter\MagentoStock\Actions\ProcessStocks;
use JustBetter\MagentoStock\Actions\Retrieval\RetrieveAllStock;
use JustBetter\MagentoStock\Actions\Retrieval\RetrieveStock;
use JustBetter\MagentoStock\Actions\Retrieval\SaveStock;
use JustBetter\MagentoStock\Actions\Update\Async\UpdateBackordersAsync;
use JustBetter\MagentoStock\Actions\Update\Async\UpdateMsiStockAsync;
use JustBetter\MagentoStock\Actions\Update\Async\UpdateSimpleStockAsync;
use JustBetter\MagentoStock\Actions\Update\Async\UpdateStockAsync;
use JustBetter\MagentoStock\Actions\Update\Sync\UpdateBackorders;
use JustBetter\MagentoStock\Actions\Update\Sync\UpdateMsiStock;
use JustBetter\MagentoStock\Actions\Update\Sync\UpdateSimpleStock;
use JustBetter\MagentoStock\Actions\Update\Sync\UpdateStock;
use JustBetter\MagentoStock\Commands\Comparison\CompareStockCommand;
use JustBetter\MagentoStock\Commands\ProcessStocksCommand;
use JustBetter\MagentoStock\Commands\Retrieval\RetrieveAllStockCommand;
use JustBetter\MagentoStock\Commands\Retrieval\RetrieveStockCommand;
use JustBetter\MagentoStock\Commands\Update\UpdateAllStockCommand;
use JustBetter\MagentoStock\Commands\Update\UpdateStockCommand;
use JustBetter\MagentoStock\Listeners\BulkOperationStatusListener;

class ServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/magento-stock.php', 'magento-stock');

        $this->registerActions();
    }

    protected function registerActions(): static
    {
        RetrieveAllStock::bind();
        RetrieveStock::bind();
        SaveStock::bind();

        UpdateBackorders::bind();
        UpdateMsiStock::bind();
        UpdateSimpleStock::bind();
        UpdateStock::bind();

        UpdateBackordersAsync::bind();
        UpdateMsiStockAsync::bind();
        UpdateSimpleStockAsync::bind();
        UpdateStockAsync::bind();

        ProcessStocks::bind();

        CompareStock::bind();
        CompareSimpleStock::bind();
        CompareMsiStock::bind();

        return $this;
    }

    public function boot(): void
    {
        $this
            ->bootMigrations()
            ->bootConfig()
            ->bootCommands()
            ->bootEvents();
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
                UpdateAllStockCommand::class,

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

    protected function bootEvents(): static
    {
        Event::listen(BulkOperationStatusEvent::class, BulkOperationStatusListener::class);

        return $this;
    }
}

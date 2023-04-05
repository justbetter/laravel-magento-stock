<?php

namespace JustBetter\MagentoStock\Tests;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoStock\Calculator\SimpleStockCalculator;
use JustBetter\MagentoStock\Retriever\DummySkuRetriever;
use JustBetter\MagentoStock\Retriever\DummyStockRetriever;
use JustBetter\MagentoStock\ServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Spatie\Activitylog\ActivitylogServiceProvider;

abstract class TestCase extends BaseTestCase
{
    use LazilyRefreshDatabase;

    protected function defineEnvironment($app): void
    {
        Magento::fake();

        config()->set('magento-stock.retriever.sku', DummySkuRetriever::class);
        config()->set('magento-stock.retriever.stock', DummyStockRetriever::class);
        config()->set('magento-stock.calculator', SimpleStockCalculator::class);

        config()->set('database.default', 'testbench');
        config()->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        activity()->disableLogging();
    }

    protected function getPackageProviders($app): array
    {
        return [
            ServiceProvider::class,
            \JustBetter\MagentoClient\ServiceProvider::class,
            ActivitylogServiceProvider::class,
            \JustBetter\ErrorLogger\ServiceProvider::class,
        ];
    }
}

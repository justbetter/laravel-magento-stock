<?php

namespace JustBetter\MagentoStock\Tests\Actions;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoStock\Actions\ProcessStocks;
use JustBetter\MagentoStock\Jobs\Retrieval\RetrieveStockJob;
use JustBetter\MagentoStock\Jobs\Update\UpdateStockAsyncJob;
use JustBetter\MagentoStock\Jobs\Update\UpdateStockJob;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ProcessStocksTest extends TestCase
{
    #[Test]
    public function it_dispatches_retrieval_jobs(): void
    {
        Bus::fake();

        Stock::query()->create([
            'sku' => '::sku::',
            'retrieve' => true,
        ]);

        /** @var ProcessStocks $action */
        $action = app(ProcessStocks::class);
        $action->process();

        Bus::assertDispatched(RetrieveStockJob::class);
        Bus::assertNotDispatched(UpdateStockJob::class);

    }

    #[Test]
    public function it_dispatches_update_jobs(): void
    {
        Bus::fake();

        Stock::query()->create([
            'sku' => '::sku::',
            'update' => true,
        ]);

        /** @var ProcessStocks $action */
        $action = app(ProcessStocks::class);
        $action->process();

        Bus::assertNotDispatched(RetrieveStockJob::class);
        Bus::assertDispatched(UpdateStockJob::class);
    }

    #[Test]
    public function it_dispatches_async_update_job(): void
    {
        Bus::fake();
        config()->set('magento-stock.async', true);

        Stock::query()->create([
            'sku' => '::sku::',
            'update' => true,
        ]);

        /** @var ProcessStocks $action */
        $action = app(ProcessStocks::class);
        $action->process();

        Bus::assertDispatched(UpdateStockAsyncJob::class);
    }
}

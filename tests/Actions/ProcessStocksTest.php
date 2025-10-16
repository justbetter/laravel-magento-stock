<?php

namespace JustBetter\MagentoStock\Tests\Actions;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoAsync\Enums\OperationStatus;
use JustBetter\MagentoAsync\Models\BulkRequest;
use JustBetter\MagentoClient\Contracts\ChecksMagento;
use JustBetter\MagentoProducts\Models\MagentoProduct;
use JustBetter\MagentoStock\Actions\ProcessStocks;
use JustBetter\MagentoStock\Jobs\Retrieval\RetrieveStockJob;
use JustBetter\MagentoStock\Jobs\Update\UpdateStockAsyncJob;
use JustBetter\MagentoStock\Jobs\Update\UpdateStockJob;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Tests\TestCase;
use Mockery\MockInterface;
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

        MagentoProduct::query()->create([
            'sku' => '::sku::',
            'exists_in_magento' => true,
        ]);

        Stock::query()->create([
            'sku' => '::sku::',
            'update' => true,
        ]);

        /** @var ProcessStocks $action */
        $action = app(ProcessStocks::class);
        $action->process();

        Bus::assertDispatched(UpdateStockAsyncJob::class);
    }

    #[Test]
    public function it_does_not_dispatch_stocks_with_open_async_operations(): void
    {
        Bus::fake();
        config()->set('magento-stock.async', true);

        MagentoProduct::query()->create([
            'sku' => '::sku_1::',
            'exists_in_magento' => true,
        ]);

        MagentoProduct::query()->create([
            'sku' => '::sku_2::',
            'exists_in_magento' => true,
        ]);

        /** @var Stock $stock */
        $stock = Stock::query()->create([
            'sku' => '::sku_1::',
            'update' => true,
            'created_at' => now()->subMinutes(10),
        ]);

        /** @var BulkRequest $request */
        $request = BulkRequest::query()->create([
            'magento_connection' => '::magento-connection::',
            'store_code' => '::store-code::',
            'method' => 'POST',
            'path' => '::path::',
            'bulk_uuid' => '::bulk-uuid-1::',
            'request' => [
                [
                    'call-1',
                ],
            ],
            'response' => [],
            'created_at' => now(),
        ]);

        $request->operations()->create([
            'operation_id' => 0,
            'subject_type' => $stock->getMorphClass(),
            'subject_id' => $stock->getKey(),
            'status' => OperationStatus::Open,
        ]);

        Stock::query()->create([
            'sku' => '::sku_2::',
            'update' => true,
        ]);

        /** @var ProcessStocks $action */
        $action = app(ProcessStocks::class);
        $action->process();

        Bus::assertDispatched(UpdateStockAsyncJob::class, function (UpdateStockAsyncJob $job): bool {
            return $job->stocks->count() === 1 && $job->stocks->first()?->sku === '::sku_2::';
        });
    }

    #[Test]
    public function it_dispatches_stocks_with_stale_async_operations(): void
    {
        Bus::fake();
        config()->set('magento-stock.async', true);
        config()->set('magento-stock.async_stale_hours', 24);

        MagentoProduct::query()->create([
            'sku' => '::sku::',
            'exists_in_magento' => true,
        ]);

        /** @var Stock $stock */
        $stock = Stock::query()->create([
            'sku' => '::sku::',
            'update' => true,
        ]);

        /** @var BulkRequest $request */
        $request = BulkRequest::query()->create([
            'magento_connection' => '::magento-connection::',
            'store_code' => '::store-code::',
            'method' => 'POST',
            'path' => '::path::',
            'bulk_uuid' => '::bulk-uuid::',
            'request' => [
                [
                    'call-1',
                ],
            ],
            'response' => [],
            'created_at' => now()->subHours(25),
        ]);

        $request->operations()->create([
            'operation_id' => 0,
            'subject_type' => $stock->getMorphClass(),
            'subject_id' => $stock->getKey(),
            'status' => OperationStatus::Open,
            'created_at' => now()->subHours(25),
        ]);

        /** @var ProcessStocks $action */
        $action = app(ProcessStocks::class);
        $action->process();

        Bus::assertDispatched(UpdateStockAsyncJob::class, function (UpdateStockAsyncJob $job): bool {
            return $job->stocks->count() === 1 && $job->stocks->first()?->sku === '::sku::';
        });
    }

    #[Test]
    public function it_does_not_dispatch_update_jobs_if_magento_is_unavailable(): void
    {
        Bus::fake();

        $this->mock(ChecksMagento::class, function (MockInterface $mock): void {
            $mock->shouldReceive('available')->andReturnFalse();
        });

        Stock::query()->create([
            'sku' => '::sku::',
            'update' => true,
        ]);

        /** @var ProcessStocks $action */
        $action = app(ProcessStocks::class);
        $action->process();

        Bus::assertNotDispatched(UpdateStockJob::class);
    }
}

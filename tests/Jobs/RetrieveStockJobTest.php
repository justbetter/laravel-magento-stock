<?php

namespace JustBetter\MagentoStock\Tests\Jobs;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoStock\Contracts\Retrieval\RetrievesStock;
use JustBetter\MagentoStock\Data\StockData;
use JustBetter\MagentoStock\Jobs\ProcessStockJob;
use JustBetter\MagentoStock\Jobs\Retrieval\RetrieveStockJob;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Tests\TestCase;

class NullRetriever implements RetrievesStock
{
    public function retrieve(string $sku): ?StockData
    {
        return null;
    }
}

class RetrieveStockJobTest extends TestCase
{
    public function test_it_dispatches_process_job(): void
    {
        Bus::fake([ProcessStockJob::class]);

        RetrieveStockJob::dispatch('::sku::');

        Bus::assertDispatched(ProcessStockJob::class, function (ProcessStockJob $job) {
            return $job->stock->sku === '::sku::';
        });
    }

    public function test_it_sets_retrieve_to_false_if_null(): void
    {
        $model = Stock::query()->create(['sku' => '::sku::', 'retrieve' => true]);

        config()->set('magento-stock.retriever.stock', NullRetriever::class);

        RetrieveStockJob::dispatch('::sku::');

        $this->assertFalse($model->refresh()->retrieve);
    }

    public function test_unique_id_and_tags(): void
    {
        $job = new RetrieveStockJob('::sku::');

        $this->assertEquals('::sku::', $job->uniqueId());
        $this->assertEquals(['::sku::'], $job->tags());
    }
}

<?php

namespace JustBetter\MagentoStock\Tests\Jobs;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoStock\Jobs\Retrieval\RetrieveAllStockJob;
use JustBetter\MagentoStock\Jobs\Retrieval\RetrieveStockJob;
use JustBetter\MagentoStock\Jobs\RetrieveUpdatedStockJob;
use JustBetter\MagentoStock\Tests\TestCase;

class SkuRetrieverJobsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake([RetrieveStockJob::class]);
    }

    public function test_it_retrieves_all_skus(): void
    {
        RetrieveAllStockJob::dispatchsync();

        Bus::assertDispatched(RetrieveStockJob::class, function (RetrieveStockJob $job) {
            return $job->sku === '::sku_1::';
        });

        Bus::assertDispatchedTimes(RetrieveStockJob::class, 2);
    }

    public function test_it_retrieves_updated_skus(): void
    {
        RetrieveUpdatedStockJob::dispatchsync();

        Bus::assertDispatched(RetrieveStockJob::class, function (RetrieveStockJob $job) {
            return $job->sku === '::sku_1::';
        });

        Bus::assertDispatchedTimes(RetrieveStockJob::class);
    }
}

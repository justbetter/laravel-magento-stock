<?php

namespace JustBetter\MagentoStock\Tests\Jobs;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoStock\Jobs\Comparison\CompareStockJob;
use JustBetter\MagentoStock\Jobs\Comparison\DispatchComparisonsJob;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Tests\TestCase;

class DispatchComparisonsJobTest extends TestCase
{
    public function test_it_dispatches_jobs(): void
    {
        Bus::fake([CompareStockJob::class]);

        Stock::query()->create([
            'sku' => '::sku::',
        ]);

        DispatchComparisonsJob::dispatch();

        Bus::assertBatchCount(1);
    }
}

<?php

namespace JustBetter\MagentoStock\Tests\Jobs;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoStock\Jobs\CompareStockJob;
use JustBetter\MagentoStock\Jobs\DispatchComparisonsJob;
use JustBetter\MagentoStock\Models\MagentoStock;
use JustBetter\MagentoStock\Tests\TestCase;

class DispatchComparisonsJobTest extends TestCase
{
    public function test_it_dispatches_jobs(): void
    {
        Bus::fake([CompareStockJob::class]);

        MagentoStock::query()->create([
            'sku' => '::sku::',
        ]);

        DispatchComparisonsJob::dispatch();

        Bus::assertBatchCount(1);
    }
}

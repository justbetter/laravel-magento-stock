<?php

namespace JustBetter\MagentoStock\Tests\Jobs\Comparison;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Testing\Fakes\PendingBatchFake;
use JustBetter\MagentoStock\Jobs\Comparison\CompareStockJob;
use JustBetter\MagentoStock\Jobs\Comparison\DispatchComparisonsJob;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DispatchComparisonsJobTest extends TestCase
{
    #[Test]
    public function it_dispatches_comparisons(): void
    {
        Bus::fake([CompareStockJob::class]);

        Stock::query()->create([
            'sku' => '::sku::',
        ]);

        DispatchComparisonsJob::dispatch();

        Bus::assertBatched(function (PendingBatchFake $batchFake): bool {
            return $batchFake->jobs->count() === 1;
        });
    }
}

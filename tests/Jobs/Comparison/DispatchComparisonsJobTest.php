<?php

declare(strict_types=1);

namespace JustBetter\MagentoStock\Tests\Jobs\Comparison;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Testing\Fakes\PendingBatchFake;
use JustBetter\MagentoProducts\Models\MagentoProduct;
use JustBetter\MagentoStock\Jobs\Comparison\CompareStockJob;
use JustBetter\MagentoStock\Jobs\Comparison\DispatchComparisonsJob;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class DispatchComparisonsJobTest extends TestCase
{
    #[Test]
    public function it_dispatches_comparisons(): void
    {
        Bus::fake([CompareStockJob::class]);

        Stock::query()->create([
            'sku' => '::sku_1::',
        ]);

        Stock::query()->create([
            'sku' => '::sku_2::',
        ]);

        MagentoProduct::query()->create([
            'sku' => '::sku_1::',
            'exists_in_magento' => true,
        ]);

        DispatchComparisonsJob::dispatch();

        Bus::assertBatched(fn (PendingBatchFake $batchFake): bool => $batchFake->jobs->count() === 1);
    }
}

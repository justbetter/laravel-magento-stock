<?php

namespace JustBetter\MagentoStock\Tests\Actions\Retrieval;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoStock\Actions\Retrieval\RetrieveAllStock;
use JustBetter\MagentoStock\Jobs\Retrieval\RetrieveStockJob;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Tests\Fakes\FakeRepository;
use JustBetter\MagentoStock\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RetrieveAllStockTest extends TestCase
{
    #[Test]
    public function it_dispatches_jobs(): void
    {
        config()->set('magento-stock.repository', FakeRepository::class);
        Bus::fake();

        /** @var RetrieveAllStock $action */
        $action = app(RetrieveAllStock::class);
        $action->retrieve(null, false);

        Bus::assertDispatched(RetrieveStockJob::class);
    }

    #[Test]
    public function it_defers_retrievals(): void
    {
        config()->set('magento-stock.repository', FakeRepository::class);

        Bus::fake();

        Stock::query()->create(['sku' => 'sku_1', 'retrieve' => false]);

        /** @var RetrieveAllStock $action */
        $action = app(RetrieveAllStock::class);
        $action->retrieve(null, true);

        Bus::assertNotDispatched(RetrieveStockJob::class);

        $stocks = Stock::query()
            ->where('retrieve', '=', true)
            ->pluck('sku');

        $this->assertEquals([
            'sku_1',
            'sku_2',
        ], $stocks->toArray());
    }
}

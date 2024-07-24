<?php

namespace JustBetter\MagentoStock\Tests\Actions\Retrieval;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoStock\Actions\Retrieval\RetrieveStock;
use JustBetter\MagentoStock\Jobs\Retrieval\SaveStockJob;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Tests\Fakes\FakeNullRepository;
use JustBetter\MagentoStock\Tests\Fakes\FakeRepository;
use JustBetter\MagentoStock\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RetrieveStockTest extends TestCase
{
    #[Test]
    public function it_sets_retrieve_when_no_stockdata(): void
    {
        config()->set('magento-stock.repository', FakeNullRepository::class);

        /** @var Stock $model */
        $model = Stock::query()
            ->create([
                'sku' => '::sku::',
                'quantity' => 10,
                'backorders' => false,
                'in_stock' => true,
                'retrieve' => true,
            ]);

        /** @var RetrieveStock $action */
        $action = app(RetrieveStock::class);
        $action->retrieve('::sku::', false);

        $this->assertFalse($model->refresh()->retrieve);
    }

    #[Test]
    public function it_dispatches_save_job(): void
    {
        config()->set('magento-stock.repository', FakeRepository::class);
        Bus::fake();

        Stock::query()
            ->create([
                'sku' => '::sku::',
                'quantity' => 10,
                'backorders' => false,
                'in_stock' => true,
                'retrieve' => true,
            ]);

        /** @var RetrieveStock $action */
        $action = app(RetrieveStock::class);
        $action->retrieve('::sku::', true);

        Bus::assertDispatched(SaveStockJob::class, function (SaveStockJob $job): bool {
            return $job->data['sku'] === '::sku::' && $job->forceUpdate;
        });
    }
}

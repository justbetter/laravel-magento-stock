<?php

namespace JustBetter\MagentoStock\Tests\Actions;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoStock\Actions\ProcessStocks;
use JustBetter\MagentoStock\Jobs\Retrieval\RetrieveStockJob;
use JustBetter\MagentoStock\Jobs\Update\UpdateStockJob;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Tests\TestCase;

class SyncStockTest extends TestCase
{
    public function test_it_syncs_stock(): void
    {
        Bus::fake();

        Stock::query()->create([
            'sku' => '::sku::',
            'retrieve' => true,
        ]);

        Stock::query()->create([
            'sku' => '::sku_2::',
            'update' => true,
        ]);

        /** @var SyncStock $action */
        $action = app(ProcessStocks::class);

        $action->sync();

        Bus::assertDispatched(RetrieveStockJob::class);
        Bus::assertDispatched(UpdateStockJob::class);
    }
}

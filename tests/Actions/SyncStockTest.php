<?php

namespace JustBetter\MagentoStock\Tests\Actions;

use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoStock\Actions\SyncStock;
use JustBetter\MagentoStock\Jobs\RetrieveStockJob;
use JustBetter\MagentoStock\Jobs\UpdateStockJob;
use JustBetter\MagentoStock\Models\MagentoStock;
use JustBetter\MagentoStock\Tests\TestCase;

class SyncStockTest extends TestCase
{
    public function test_it_syncs_stock(): void
    {
        Bus::fake();

        MagentoStock::query()->create([
            'sku' => '::sku::',
            'retrieve' => true,
        ]);

        MagentoStock::query()->create([
            'sku' => '::sku_2::',
            'update' => true,
        ]);

        /** @var SyncStock $action */
        $action = app(SyncStock::class);

        $action->sync();

        Bus::assertDispatched(RetrieveStockJob::class);
        Bus::assertDispatched(UpdateStockJob::class);
    }
}

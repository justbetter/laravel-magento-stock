<?php

namespace JustBetter\MagentoStock\Tests\Listeners;

use JustBetter\MagentoStock\Events\StockChanged;
use JustBetter\MagentoStock\Models\MagentoStock;
use JustBetter\MagentoStock\Tests\TestCase;

class SetStockRetrieveListenerTest extends TestCase
{
    public function test_it_sets_retrieve(): void
    {
        /** @var MagentoStock $stock */
        $stock = MagentoStock::query()->create([
            'sku' => '::sku::',
        ]);

        event(new StockChanged($stock));

        $this->assertTrue($stock->refresh()->retrieve);
    }
}

<?php

namespace JustBetter\MagentoStock\Tests\Listeners;

use JustBetter\MagentoStock\Events\StockChangedEvent;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Tests\TestCase;

class SetStockRetrieveListenerTest extends TestCase
{
    public function test_it_sets_retrieve(): void
    {
        /** @var MagentoStock $stock */
        $stock = Stock::query()->create([
            'sku' => '::sku::',
        ]);

        event(new StockChangedEvent($stock));

        $this->assertTrue($stock->refresh()->retrieve);
    }
}

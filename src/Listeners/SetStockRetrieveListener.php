<?php

namespace JustBetter\MagentoStock\Listeners;

use JustBetter\MagentoStock\Events\StockChanged;

class SetStockRetrieveListener
{
    public function handle(StockChanged $event): void
    {
        $event->stock->update([
            'retrieve' => true,
        ]);
    }
}

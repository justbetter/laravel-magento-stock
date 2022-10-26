<?php

namespace JustBetter\MagentoStock\Listeners;

use JustBetter\MagentoStock\Events\StockChangedEvent;

class SetStockRetrieveListener
{
    public function handle(StockChangedEvent $event): void
    {
        $event->stock->update([
            'retrieve' => true,
        ]);
    }
}

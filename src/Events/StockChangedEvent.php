<?php

namespace JustBetter\MagentoStock\Events;

use Illuminate\Foundation\Events\Dispatchable;
use JustBetter\MagentoStock\Models\MagentoStock;

class StockChangedEvent
{
    use Dispatchable;

    public function __construct(public MagentoStock $stock)
    {
    }
}

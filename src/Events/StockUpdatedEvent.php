<?php

namespace JustBetter\MagentoStock\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JustBetter\MagentoStock\Models\Stock;

class StockUpdatedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public Stock $stock)
    {
    }
}

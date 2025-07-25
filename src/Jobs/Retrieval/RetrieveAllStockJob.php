<?php

namespace JustBetter\MagentoStock\Jobs\Retrieval;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Carbon;
use JustBetter\MagentoStock\Contracts\Retrieval\RetrievesAllStock;

class RetrieveAllStockJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(public ?Carbon $from = null, public bool $defer = true)
    {
        $this->onQueue(config()->string('magento-stock.queue'));
    }

    public function handle(RetrievesAllStock $stock): void
    {
        $stock->retrieve($this->from, $this->defer);
    }
}

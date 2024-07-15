<?php

namespace JustBetter\MagentoStock\Jobs\Update;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use JustBetter\MagentoStock\Contracts\Update\Async\UpdatesStockAsync;

class UpdateStockAsyncJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public Collection $stocks)
    {
        $this->onQueue(config('magento-stock.queue'));
    }

    public function handle(UpdatesStockAsync $stock): void
    {
        $stock->update($this->stocks);
    }
}

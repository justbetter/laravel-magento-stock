<?php

namespace JustBetter\MagentoStock\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use JustBetter\MagentoStock\Contracts\ProcessesStocks;

class ProcessStocksJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct()
    {
        $this->onQueue(config('magento-stock.queue'));
    }

    public function handle(ProcessesStocks $contract): void
    {
        $contract->process();
    }
}

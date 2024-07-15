<?php

namespace JustBetter\MagentoStock\Jobs\Comparison;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoStock\Models\Stock;

class DispatchComparisonsJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct()
    {
        $this->onQueue(config('magento-stock.queue'));
    }

    public function handle(): void
    {
        $batch = Stock::query()
            ->get()
            ->mapInto(CompareStockJob::class);

        Bus::batch($batch)
            ->name('Stock Compare')
            ->allowFailures()
            ->onQueue(config('magento-stock.compare_queue'))
            ->dispatch();
    }
}

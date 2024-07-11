<?php

namespace JustBetter\MagentoStock\Jobs\Comparinson;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoStock\Models\Stock;

class DispatchComparisonsJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct()
    {
        $this->onQueue(config('magento-stock.queue'));
    }

    public function handle(): void
    {
        $batch = Stock::query()
            ->select('sku')
            ->pluck('sku')
            ->mapInto(CompareStockJob::class);

        Bus::batch($batch)
            ->name('Stock Compare')
            ->allowFailures()
            ->onQueue(config('magento-stock.compare_queue'))
            ->dispatch();
    }
}

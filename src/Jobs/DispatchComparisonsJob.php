<?php

namespace JustBetter\MagentoStock\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;
use JustBetter\MagentoStock\Models\MagentoStock;

class DispatchComparisonsJob implements ShouldQueue, ShouldBeUnique
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
        $batch = MagentoStock::query()
            ->select('sku')
            ->get()
            ->map(fn (MagentoStock $stock) => new CompareStockJob($stock->sku));

        Bus::batch($batch)
            ->name('Stock Compare')
            ->allowFailures()
            ->onQueue(config('magento-stock.compare_queue'))
            ->dispatch();
    }
}

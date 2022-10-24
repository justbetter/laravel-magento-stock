<?php

namespace JustBetter\MagentoStock\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use JustBetter\MagentoStock\Contracts\RetrievesStockSkus;

class RetrieveUpdatedStockJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public int $maxExceptions = 1;

    public int $timeout = 3600;

    public function __construct(public ?Carbon $from = null)
    {
        $this->onQueue(config('magento-stock.queue'));
    }

    public function handle(): void
    {
        /** @var RetrievesStockSkus $retriever */
        $retriever = app(config('magento-stock.retriever.sku'));

        $retriever->retrieveUpdated($this->from)
            ->each(fn (string $sku) => RetrieveStockJob::dispatch($sku));
    }

    public function uniqueId(): string
    {
        return 'stock:retrieve:updated';
    }
}

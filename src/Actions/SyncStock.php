<?php

namespace JustBetter\MagentoStock\Actions;

use JustBetter\MagentoStock\Contracts\SyncsStock;
use JustBetter\MagentoStock\Jobs\RetrieveStockJob;
use JustBetter\MagentoStock\Jobs\UpdateStockJob;
use JustBetter\MagentoStock\Models\MagentoStock;

class SyncStock implements SyncsStock
{
    public function sync(): void
    {
        MagentoStock::shouldRetrieve()
            ->select(['sku'])
            ->take(config('magento-stock.retrieve_limit', 25))
            ->get()
            ->each(fn (MagentoStock $stock) => RetrieveStockJob::dispatch($stock->sku));

        MagentoStock::shouldUpdate()
            ->select(['id', 'sku'])
            ->take(config('magento-stock.update_limit', 25))
            ->get()
            ->each(fn (MagentoStock $stock) => UpdateStockJob::dispatch($stock->sku));
    }

    public static function bind(): void
    {
        app()->singleton(SyncsStock::class, static::class);
    }
}

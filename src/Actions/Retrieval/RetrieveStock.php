<?php

namespace JustBetter\MagentoStock\Actions\Retrieval;

use JustBetter\MagentoStock\Contracts\Retrieval\RetrievesStock;
use JustBetter\MagentoStock\Jobs\Retrieval\SaveStockJob;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Repositories\BaseRepository;

class RetrieveStock implements RetrievesStock
{
    public function retrieve(string $sku, bool $forceUpdate): void
    {
        $repository = BaseRepository::resolve();

        $stockData = $repository->retrieve($sku);

        if ($stockData === null) {
            Stock::query()
                ->where('sku', '=', $sku)
                ->update(['retrieve' => false]);

            return;
        }

        SaveStockJob::dispatch($stockData, $forceUpdate);
    }

    public static function bind(): void
    {
        app()->singleton(RetrievesStock::class, static::class);
    }
}

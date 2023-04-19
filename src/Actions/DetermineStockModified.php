<?php

namespace JustBetter\MagentoStock\Actions;

use JustBetter\MagentoStock\Contracts\DeterminesStockModified;
use JustBetter\MagentoStock\Data\StockData;
use JustBetter\MagentoStock\Models\MagentoStock;

class DetermineStockModified implements DeterminesStockModified
{
    public function modified(StockData $stockData): bool
    {
        /** @var ?MagentoStock $current */
        $current = MagentoStock::query()
            ->where('sku', '=', $stockData->sku)
            ->first();

        if ($current === null || $current->last_updated === null) {
            return true;
        }

        return ! StockData::fromModel($current)->equals($stockData);
    }

    public static function bind(): void
    {
        app()->singleton(DeterminesStockModified::class, static::class);
    }
}

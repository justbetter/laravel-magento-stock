<?php

namespace JustBetter\MagentoStock\Actions\Retrieval;

use JustBetter\MagentoStock\Contracts\Retrieval\SavesStock;
use JustBetter\MagentoStock\Data\StockData;
use JustBetter\MagentoStock\Models\Stock;

class SaveStock implements SavesStock
{
    public function save(StockData $stock, bool $forceUpdate): void
    {
        /** @var Stock $model */
        $model = Stock::query()->firstOrCreate([
            'sku' => $stock['sku'],
        ]);

        $model->in_stock = $stock['in_stock'];
        $model->quantity = $stock['quantity'];
        $model->backorders = $stock['backorders'];

        $model->msi_stock = $stock['msi_quantity'];
        $model->msi_status = $stock['msi_status'];

        $model->sync = true;
        $model->retrieve = false;
        $model->last_retrieved = now();

        $model->update = $forceUpdate || $model->checksum !== $stock->checksum();
        $model->checksum = $stock->checksum();

        $model->save();
    }

    public static function bind(): void
    {
        app()->singleton(SavesStock::class, static::class);
    }
}

<?php

namespace JustBetter\MagentoStock\Actions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\PendingDispatch;
use JustBetter\MagentoStock\Contracts\ProcessesStocks;
use JustBetter\MagentoStock\Jobs\Retrieval\RetrieveStockJob;
use JustBetter\MagentoStock\Jobs\Update\UpdateStockAsyncJob;
use JustBetter\MagentoStock\Jobs\Update\UpdateStockJob;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Repositories\BaseRepository;

class ProcessStocks implements ProcessesStocks
{
    public function process(): void
    {
        $repository = BaseRepository::resolve();

        Stock::query()
            ->where('sync', '=', true)
            ->where('retrieve', '=', true)
            ->select(['sku'])
            ->take($repository->retrieveLimit())
            ->get()
            ->each(fn (Stock $stock): PendingDispatch => RetrieveStockJob::dispatch($stock->sku));

        if (config('magento-stock.async')) {
            $stocks = Stock::query()
                ->where('sync', '=', true)
                ->where('update', '=', true)
                ->whereHas('product', function (Builder $query): void {
                    $query->where('exists_in_magento', '=', true);
                })
                ->select(['id', 'sku'])
                ->take($repository->updateLimit())
                ->get();

            UpdateStockAsyncJob::dispatch($stocks);
        } else {
            Stock::query()
                ->where('sync', '=', true)
                ->where('update', '=', true)
                ->select(['id', 'sku'])
                ->take($repository->updateLimit())
                ->get()
                ->each(fn (Stock $stock): PendingDispatch => UpdateStockJob::dispatch($stock));
        }
    }

    public static function bind(): void
    {
        app()->singleton(ProcessesStocks::class, static::class);
    }
}

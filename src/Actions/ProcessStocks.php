<?php

namespace JustBetter\MagentoStock\Actions;

use Illuminate\Foundation\Bus\PendingDispatch;
use JustBetter\MagentoStock\Contracts\ProcessesStocks;
use JustBetter\MagentoStock\Jobs\Retrieval\RetrieveStockJob;
use JustBetter\MagentoStock\Jobs\Update\UpdateStockJob;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Repositories\BaseRepository;

class ProcessStocks implements ProcessesStocks
{
    public function process(): void
    {
        $repository = BaseRepository::resolve();

        Stock::query()
            ->where('retrieve', '=', true)
            ->select(['sku'])
            ->take($repository->retrieveLimit())
            ->get()
            ->each(fn (Stock $stock): PendingDispatch => RetrieveStockJob::dispatch($stock->sku));


        if (config('magento-stocks.async')) {
            $stocks = Stock::query()
                ->where('update', '=', true)
                ->select(['id', 'sku'])
                ->take($repository->updateLimit())
                ->get();



        } else {
            Stock::query()
                ->where('update', '=', true)
                ->select(['id', 'sku'])
                ->take($repository->updateLimit())
                ->get()
                ->each(fn (Stock $stock): PendingDispatch => UpdateStockJob::dispatch($stock->sku));
        }


    }

    public static function bind(): void
    {
        app()->singleton(ProcessesStocks::class, static::class);
    }
}

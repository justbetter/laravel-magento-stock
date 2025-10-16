<?php

namespace JustBetter\MagentoStock\Actions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\PendingDispatch;
use JustBetter\MagentoAsync\Enums\OperationStatus;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoStock\Contracts\ProcessesStocks;
use JustBetter\MagentoStock\Jobs\Retrieval\RetrieveStockJob;
use JustBetter\MagentoStock\Jobs\Update\UpdateStockAsyncJob;
use JustBetter\MagentoStock\Jobs\Update\UpdateStockJob;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Repositories\BaseRepository;

class ProcessStocks implements ProcessesStocks
{
    public function __construct(protected Magento $magento) {}

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

        if (! $this->magento->available()) {
            return;
        }

        if (config('magento-stock.async')) {
            $stocks = Stock::query()
                ->where('sync', '=', true)
                ->where('update', '=', true)
                ->whereHas('product', function (Builder $query): void {
                    $query->where('exists_in_magento', '=', true);
                })
                ->whereDoesntHave('bulkOperations', function (Builder $query): void {
                    $staleHours = config()->integer('magento-stock.async_stale_hours', 24);
                    $staleThreshold = now()->subHours($staleHours);

                    $query
                        ->where(function (Builder $query): void {
                            $query
                                ->where('status', '=', OperationStatus::Open)
                                ->orWhereNull('status');
                        })
                        ->where('created_at', '>=', $staleThreshold);
                })
                ->select(['id', 'sku'])
                ->take($repository->updateLimit())
                ->get();

            UpdateStockAsyncJob::dispatchIf($stocks->isNotEmpty(), $stocks);
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

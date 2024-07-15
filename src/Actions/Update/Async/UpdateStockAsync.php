<?php

namespace JustBetter\MagentoStock\Actions\Update\Async;

use Illuminate\Support\Collection;
use JustBetter\MagentoProducts\Contracts\ChecksMagentoExistence;
use JustBetter\MagentoStock\Contracts\Update\Async\UpdatesBackordersAsync;
use JustBetter\MagentoStock\Contracts\Update\Async\UpdatesMsiStockAsync;
use JustBetter\MagentoStock\Contracts\Update\Async\UpdatesSimpleStockAsync;
use JustBetter\MagentoStock\Contracts\Update\Async\UpdatesStockAsync;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Repositories\BaseRepository;

class UpdateStockAsync implements UpdatesStockAsync
{
    public function __construct(
        protected ChecksMagentoExistence $existence,
        protected UpdatesBackordersAsync $backorders,
        protected UpdatesSimpleStockAsync $simpleStock,
        protected UpdatesMsiStockAsync $msiStock
    ) {
    }

    public function update(Collection $stocks): void
    {
        $repository = BaseRepository::resolve();

        if ($repository->backorders()) {
            $this->backorders->update($stocks);
        }

        if ($repository->msi()) {
            $this->msiStock->update($stocks);
        } else {
            $this->simpleStock->update($stocks);
        }

        $stocks->each(fn (Stock $stock) => $stock->update([
            'update' => false,
        ]));
    }

    public static function bind(): void
    {
        app()->singleton(UpdatesStockAsync::class, static::class);
    }
}

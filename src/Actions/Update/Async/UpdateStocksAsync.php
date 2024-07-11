<?php

namespace JustBetter\MagentoStock\Actions\Update\Async;

use Illuminate\Support\Collection;
use JustBetter\MagentoProducts\Contracts\ChecksMagentoExistence;
use JustBetter\MagentoStock\Contracts\Update\Async\UpdatesBackordersAsync;
use JustBetter\MagentoStock\Contracts\Update\Async\UpdatesStocksAsync;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Repositories\BaseRepository;

class UpdateStocksAsync implements UpdatesStocksAsync
{
    public function __construct(
        protected ChecksMagentoExistence $existence,
        protected UpdatesBackordersAsync $backorders
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
}
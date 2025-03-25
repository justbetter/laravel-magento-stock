<?php

namespace JustBetter\MagentoStock\Actions\Update\Sync;

use JustBetter\MagentoProducts\Contracts\ChecksMagentoExistence;
use JustBetter\MagentoStock\Contracts\Update\Sync\UpdatesBackorders;
use JustBetter\MagentoStock\Contracts\Update\Sync\UpdatesMsiStock;
use JustBetter\MagentoStock\Contracts\Update\Sync\UpdatesSimpleStock;
use JustBetter\MagentoStock\Contracts\Update\Sync\UpdatesStock;
use JustBetter\MagentoStock\Events\StockUpdatedEvent;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Repositories\BaseRepository;

class UpdateStock implements UpdatesStock
{
    public function __construct(
        protected ChecksMagentoExistence $existence,
        protected UpdatesBackorders $backorders,
        protected UpdatesSimpleStock $simpleStock,
        protected UpdatesMsiStock $msiStock,
    ) {}

    public function update(Stock $stock): void
    {
        if (! $this->existence->exists($stock->sku)) {
            $stock->update([
                'update' => false,
            ]);

            activity()
                ->on($stock)
                ->log('Not updating, product does not exist in Magento');

            return;
        }

        $repository = BaseRepository::resolve();

        if ($repository->backorders()) {
            $this->backorders->update($stock);
        }

        if ($repository->msi()) {
            $this->msiStock->update($stock);
        } else {
            $this->simpleStock->update($stock);
        }

        $stock->update([
            'update' => false,
            'last_updated' => now(),
            'last_failed' => null,
            'fail_count' => 0,
        ]);

        event(new StockUpdatedEvent($stock));
    }

    public static function bind(): void
    {
        app()->singleton(UpdatesStock::class, static::class);
    }
}

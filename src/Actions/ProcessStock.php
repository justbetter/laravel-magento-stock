<?php

namespace JustBetter\MagentoStock\Actions;

use JustBetter\MagentoStock\Contracts\DeterminesStockModified;
use JustBetter\MagentoStock\Contracts\ProcessesStock;
use JustBetter\MagentoStock\Contracts\ResolvesStockCalculator;
use JustBetter\MagentoStock\Data\StockData;

class ProcessStock implements ProcessesStock
{
    public function __construct(
        protected ResolvesStockCalculator $stockCalculator,
        protected DeterminesStockModified $stockModified,
    ) {
    }

    public function process(StockData $stock, bool $forceUpdate = false): void
    {
        $calculator = $this->stockCalculator->resolve($stock);

        $stock->setQuantity($calculator->calculateQuantity());
        $stock->setInStock($calculator->isInStock());
        $stock->setBackorders($calculator->getBackorders());
        $stock->setMsiQuantities($calculator->calculateMsiQuantities());
        $stock->setMsiStatusses($calculator->calculateMsiStatusses());

        $model = $stock->toModel();

        $model->sync = true;
        $model->retrieve = false;
        $model->last_retrieved = now();

        $model->update = $forceUpdate || $this->stockModified->modified($stock);

        $model->save();
    }

    public static function bind(): void
    {
        app()->singleton(ProcessesStock::class, static::class);
    }
}

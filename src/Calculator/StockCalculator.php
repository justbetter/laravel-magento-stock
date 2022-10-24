<?php

namespace JustBetter\MagentoStock\Calculator;

use JustBetter\MagentoStock\Data\StockData;

abstract class StockCalculator
{
    public function __construct(public StockData $stock)
    {
    }

    public function getBackorders(): bool
    {
        return $this->stock->backorders;
    }

    public function calculateQuantity(): float
    {
        return $this->stock->quantity;
    }

    public function calculateMsiQuantities(): array
    {
        return $this->stock->msiQuantity;
    }

    public function calculateMsiStatusses(): array
    {
        return $this->stock->msiStatus;
    }

    public function isInStock(): bool
    {
        if (config('magento-stock.msi', false)) {
            return collect($this->stock->msiQuantity)
                ->reject(fn (float $q) => $q == 0)
                ->isNotEmpty();
        }

        return $this->calculateQuantity() > 0;
    }
}

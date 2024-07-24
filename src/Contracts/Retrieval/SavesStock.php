<?php

namespace JustBetter\MagentoStock\Contracts\Retrieval;

use JustBetter\MagentoStock\Data\StockData;

interface SavesStock
{
    public function save(StockData $stock, bool $forceUpdate): void;
}

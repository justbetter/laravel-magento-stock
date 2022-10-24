<?php

namespace JustBetter\MagentoStock\Contracts;

use JustBetter\MagentoStock\Data\StockData;

interface ProcessesStock
{
    public function process(StockData $stock, bool $forceUpdate): void;
}

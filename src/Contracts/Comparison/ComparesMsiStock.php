<?php

namespace JustBetter\MagentoStock\Contracts\Comparison;

use JustBetter\MagentoStock\Models\Stock;

interface ComparesMsiStock
{
    public function compare(Stock $stock): void;
}

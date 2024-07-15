<?php

namespace JustBetter\MagentoStock\Contracts\Comparison;

use JustBetter\MagentoStock\Models\Stock;

interface ComparesSimpleStock
{
    public function compare(Stock $stock): void;
}

<?php

namespace JustBetter\MagentoStock\Contracts\Comparinson;

use JustBetter\MagentoStock\Models\Stock;

interface ComparesSimpleStock
{
    public function compare(Stock $stock): void;
}

<?php

namespace JustBetter\MagentoStock\Contracts\Comparinson;

use JustBetter\MagentoStock\Models\Stock;

interface ComparesMsiStock
{
    public function compare(Stock $stock): void;
}

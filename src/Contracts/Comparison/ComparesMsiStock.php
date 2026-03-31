<?php

declare(strict_types=1);

namespace JustBetter\MagentoStock\Contracts\Comparison;

use JustBetter\MagentoStock\Models\Stock;

interface ComparesMsiStock
{
    public function compare(Stock $stock): void;
}

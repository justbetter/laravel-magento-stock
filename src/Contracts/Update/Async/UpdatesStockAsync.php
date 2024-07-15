<?php

namespace JustBetter\MagentoStock\Contracts\Update\Async;

use Illuminate\Support\Collection;
use JustBetter\MagentoStock\Models\Stock;

interface UpdatesStockAsync
{
    /** @param Collection<int, Stock> $stocks */
    public function update(Collection $stocks): void;
}

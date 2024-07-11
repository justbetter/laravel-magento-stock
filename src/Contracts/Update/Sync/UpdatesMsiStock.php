<?php

namespace JustBetter\MagentoStock\Contracts\Update\Sync;

use JustBetter\MagentoStock\Models\Stock;

interface UpdatesMsiStock
{
    public function update(Stock $stock): void;
}

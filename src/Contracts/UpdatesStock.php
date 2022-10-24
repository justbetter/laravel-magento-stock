<?php

namespace JustBetter\MagentoStock\Contracts;

use JustBetter\MagentoStock\Models\MagentoStock;

interface UpdatesStock
{
    public function update(MagentoStock $model): void;
}

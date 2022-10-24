<?php

namespace JustBetter\MagentoStock\Contracts;

use JustBetter\MagentoStock\Models\MagentoStock;

interface UpdatesBackorders
{
    public function update(MagentoStock $model): void;
}

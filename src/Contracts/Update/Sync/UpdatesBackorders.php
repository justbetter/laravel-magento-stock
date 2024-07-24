<?php

namespace JustBetter\MagentoStock\Contracts\Update\Sync;

use JustBetter\MagentoStock\Models\Stock;

interface UpdatesBackorders
{
    public function update(Stock $stock): void;
}

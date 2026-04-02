<?php

declare(strict_types=1);

namespace JustBetter\MagentoStock\Contracts\Update\Sync;

use JustBetter\MagentoStock\Models\Stock;

interface UpdatesStock
{
    public function update(Stock $stock): void;
}

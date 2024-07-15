<?php

namespace JustBetter\MagentoStock\Commands\Update;

use Illuminate\Console\Command;
use JustBetter\MagentoStock\Models\Stock;

class UpdateAllStockCommand extends Command
{
    protected $signature = 'magento-stock:update-all';

    protected $description = 'Update all stock to Magento';

    public function handle(): int
    {
        Stock::query()
            ->update([
                'sync' => true,
                'update' => true,
            ]);

        return static::SUCCESS;
    }
}

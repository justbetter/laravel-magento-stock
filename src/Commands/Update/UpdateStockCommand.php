<?php

namespace JustBetter\MagentoStock\Commands\Update;

use Illuminate\Console\Command;
use JustBetter\MagentoStock\Jobs\Update\UpdateStockJob;
use JustBetter\MagentoStock\Models\Stock;

class UpdateStockCommand extends Command
{
    protected $signature = 'magento-stock:update {sku}';

    protected $description = 'Update stock to Magento';

    public function handle(): int
    {
        /** @var string $sku */
        $sku = $this->argument('sku');

        /** @var Stock $stock */
        $stock = Stock::query()
            ->where('sku', '=', $sku)
            ->firstOrFail();

        UpdateStockJob::dispatch($stock);

        return static::SUCCESS;
    }
}

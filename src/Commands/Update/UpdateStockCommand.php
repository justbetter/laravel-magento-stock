<?php

namespace JustBetter\MagentoStock\Commands\Update;

use Illuminate\Console\Command;
use JustBetter\MagentoStock\Jobs\Update\UpdateStockJob;

class UpdateStockCommand extends Command
{
    protected $signature = 'magento-stock:update {sku}';

    protected $description = 'Update stock to Magento';

    public function handle(): int
    {
        /** @var string $sku */
        $sku = $this->argument('sku');

        UpdateStockJob::dispatch($sku);

        return static::SUCCESS;
    }
}

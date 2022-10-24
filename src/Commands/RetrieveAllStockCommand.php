<?php

namespace JustBetter\MagentoStock\Commands;

use Illuminate\Console\Command;
use JustBetter\MagentoStock\Jobs\RetrieveAllStockJob;

class RetrieveAllStockCommand extends Command
{
    protected $signature = 'magento:stock:retrieve-all';

    protected $description = 'Dispatch job to retrieve all stock';

    public function handle(): int
    {
        $this->info('Dispatching...');

        RetrieveAllStockJob::dispatch();

        $this->info('Done!');

        return static::SUCCESS;
    }
}

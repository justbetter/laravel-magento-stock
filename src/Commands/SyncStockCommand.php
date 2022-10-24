<?php

namespace JustBetter\MagentoStock\Commands;

use Illuminate\Console\Command;
use JustBetter\MagentoStock\Jobs\SyncStockJob;

class SyncStockCommand extends Command
{
    protected $signature = 'magento:stock:sync';

    protected $description = 'Dispatch job to retrieve and update stocks based on the retrieve/update flags';

    public function handle(): int
    {
        $this->info('Dispatching...');

        SyncStockJob::dispatch();

        $this->info('Done!');

        return static::SUCCESS;
    }
}

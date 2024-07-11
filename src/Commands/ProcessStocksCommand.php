<?php

namespace JustBetter\MagentoStock\Commands;

use Illuminate\Console\Command;
use JustBetter\MagentoStock\Jobs\ProcessStocksJob;

class ProcessStocksCommand extends Command
{
    protected $signature = 'magento-stock:process';

    protected $description = 'Dispatch jobs to retrieve and update stocks based on the retrieve/update flags';

    public function handle(): int
    {
        ProcessStocksJob::dispatch();

        return static::SUCCESS;
    }
}

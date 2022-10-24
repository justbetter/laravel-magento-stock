<?php

namespace JustBetter\MagentoStock\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use JustBetter\MagentoStock\Jobs\RetrieveUpdatedStockJob;

class RetrieveUpdatedStockCommand extends Command
{
    protected $signature = 'magento:stock:retrieve-updated {from?}';

    protected $description = 'Dispatch job to retrieve stock';

    public function handle(): int
    {
        $this->info('Dispatching...');

        /** @var ?string $from */
        $from = $this->argument('from');

        $date = $from !== null
            ? Carbon::parse($from)
            : null;

        RetrieveUpdatedStockJob::dispatch($date);

        $this->info('Done!');

        return static::SUCCESS;
    }
}

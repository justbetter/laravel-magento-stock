<?php

namespace JustBetter\MagentoStock\Tests\Jobs;

use JustBetter\MagentoStock\Contracts\ProcessesStocks;
use JustBetter\MagentoStock\Jobs\ProcessStocksJob;
use JustBetter\MagentoStock\Tests\TestCase;
use Mockery\MockInterface;

class SyncStockJobTest extends TestCase
{
    public function test_it_calls_action(): void
    {
        $this->mock(ProcessesStocks::class, function (MockInterface $mock) {
            $mock->shouldReceive('sync')->once();
        });

        ProcessStocksJob::dispatch();
    }
}

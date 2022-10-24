<?php

namespace JustBetter\MagentoStock\Tests\Jobs;

use JustBetter\MagentoStock\Contracts\SyncsStock;
use JustBetter\MagentoStock\Jobs\SyncStockJob;
use JustBetter\MagentoStock\Tests\TestCase;
use Mockery\MockInterface;

class SyncStockJobTest extends TestCase
{
    public function test_it_calls_action(): void
    {
        $this->mock(SyncsStock::class, function (MockInterface $mock) {
            $mock->shouldReceive('sync')->once();
        });

        SyncStockJob::dispatch();
    }
}

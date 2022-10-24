<?php

namespace JustBetter\MagentoStock\Tests\Jobs;

use JustBetter\MagentoStock\Contracts\MonitorsWaitTimes;
use JustBetter\MagentoStock\Jobs\MonitorWaitTimesJob;
use JustBetter\MagentoStock\Tests\TestCase;
use Mockery\MockInterface;

class MonitorWaitTimesJobTest extends TestCase
{
    public function test_it_calls_action(): void
    {
        $this->mock(MonitorsWaitTimes::class, function (MockInterface $mock) {
            $mock->shouldReceive('monitor')->once();
        });

        MonitorWaitTimesJob::dispatchSync();
    }
}

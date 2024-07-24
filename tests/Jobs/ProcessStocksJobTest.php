<?php

namespace JustBetter\MagentoStock\Tests\Jobs;

use JustBetter\MagentoStock\Contracts\ProcessesStocks;
use JustBetter\MagentoStock\Jobs\ProcessStocksJob;
use JustBetter\MagentoStock\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class ProcessStocksJobTest extends TestCase
{
    #[Test]
    public function it_calls_action(): void
    {
        $this->mock(ProcessesStocks::class, function (MockInterface $mock): void {
            $mock->shouldReceive('process')->once();
        });

        ProcessStocksJob::dispatch();
    }
}

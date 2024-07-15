<?php

namespace JustBetter\MagentoStock\Tests\Jobs\Update;

use JustBetter\MagentoStock\Contracts\Update\Async\UpdatesStockAsync;
use JustBetter\MagentoStock\Jobs\Update\UpdateStockAsyncJob;
use JustBetter\MagentoStock\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class UpdateStockAsyncJobTest extends TestCase
{
    #[Test]
    public function it_calls_action(): void
    {
        $this->mock(UpdatesStockAsync::class, function (MockInterface $mock): void {
            $mock->shouldReceive('update')->once();
        });

        UpdateStockAsyncJob::dispatch(collect());
    }
}

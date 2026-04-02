<?php

declare(strict_types=1);

namespace JustBetter\MagentoStock\Tests\Jobs\Retrieval;

use JustBetter\MagentoStock\Contracts\Retrieval\RetrievesAllStock;
use JustBetter\MagentoStock\Jobs\Retrieval\RetrieveAllStockJob;
use JustBetter\MagentoStock\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

final class RetrieveAllStockJobTest extends TestCase
{
    #[Test]
    public function it_calls_action(): void
    {
        $this->mock(RetrievesAllStock::class, function (MockInterface $mock): void {
            $mock->shouldReceive('retrieve')->once();
        });

        RetrieveAllStockJob::dispatch(null);
    }
}

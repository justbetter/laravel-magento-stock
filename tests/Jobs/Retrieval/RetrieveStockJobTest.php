<?php

namespace JustBetter\MagentoStock\Tests\Jobs\Retrieval;

use JustBetter\MagentoStock\Contracts\Retrieval\RetrievesStock;
use JustBetter\MagentoStock\Jobs\Retrieval\RetrieveStockJob;
use JustBetter\MagentoStock\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class RetrieveStockJobTest extends TestCase
{
    #[Test]
    public function it_calls_action(): void
    {
        $this->mock(RetrievesStock::class, function (MockInterface $mock): void {
            $mock->shouldReceive('retrieve')->once();
        });

        RetrieveStockJob::dispatch('::sku::', false);
    }

    #[Test]
    public function it_has_unique_id(): void
    {
        $job = new RetrieveStockJob('::sku::', false);

        $this->assertEquals('::sku::', $job->uniqueId());
    }

    #[Test]
    public function it_has_tags(): void
    {
        $job = new RetrieveStockJob('::sku::', false);

        $this->assertEquals(['::sku::'], $job->tags());
    }
}

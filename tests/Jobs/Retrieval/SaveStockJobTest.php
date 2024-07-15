<?php

namespace JustBetter\MagentoStock\Tests\Jobs\Retrieval;

use JustBetter\MagentoStock\Contracts\Retrieval\SavesStock;
use JustBetter\MagentoStock\Data\StockData;
use JustBetter\MagentoStock\Jobs\Retrieval\SaveStockJob;
use JustBetter\MagentoStock\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class SaveStockJobTest extends TestCase
{
    #[Test]
    public function it_calls_action(): void
    {
        $this->mock(SavesStock::class, function (MockInterface $mock): void {
            $mock->shouldReceive('update')->once();
        });

        $data = StockData::of([
            'sku' => '::sku::',
            'quantity' => 0,
            'in_stock' => false,
        ]);

        SaveStockJob::dispatch($data, false);
    }

    #[Test]
    public function it_has_unique_id(): void
    {
        $data = StockData::of([
            'sku' => '::sku::',
            'quantity' => 0,
            'in_stock' => false,
        ]);

        $job = new SaveStockJob($data, false);

        $this->assertEquals('::sku::', $job->uniqueId());
    }

    #[Test]
    public function it_has_tags(): void
    {
        $data = StockData::of([
            'sku' => '::sku::',
            'quantity' => 0,
            'in_stock' => false,
        ]);

        $job = new SaveStockJob($data, false);

        $this->assertEquals(['::sku::'], $job->tags());
    }
}

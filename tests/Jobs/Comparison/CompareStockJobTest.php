<?php

namespace JustBetter\MagentoStock\Tests\Jobs\Comparison;

use JustBetter\MagentoStock\Contracts\Comparison\ComparesStock;
use JustBetter\MagentoStock\Jobs\Comparison\CompareStockJob;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class CompareStockJobTest extends TestCase
{
    #[Test]
    public function it_calls_action(): void
    {
        $this->mock(ComparesStock::class, function (MockInterface $mock): void {
            $mock->shouldReceive('compare')->once();
        });

        /** @var Stock $model */
        $model = Stock::query()->create([
            'sku' => '::sku::',
            'update' => true,
        ]);

        CompareStockJob::dispatch($model);
    }

    #[Test]
    public function it_has_unique_id(): void
    {
        /** @var Stock $model */
        $model = Stock::query()->create([
            'sku' => '::sku::',
            'update' => true,
        ]);

        $job = new CompareStockJob($model);

        $this->assertEquals('::sku::', $job->uniqueId());
    }

    #[Test]
    public function it_has_tags(): void
    {
        /** @var Stock $model */
        $model = Stock::query()->create([
            'sku' => '::sku::',
            'update' => true,
        ]);

        $job = new CompareStockJob($model);

        $this->assertEquals(['::sku::'], $job->tags());
    }
}

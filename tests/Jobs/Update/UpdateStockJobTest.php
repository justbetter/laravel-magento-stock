<?php

namespace JustBetter\MagentoStock\Tests\Jobs\Update;

use JustBetter\MagentoStock\Contracts\Update\Sync\UpdatesStock;
use JustBetter\MagentoStock\Jobs\Update\UpdateStockJob;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class UpdateStockJobTest extends TestCase
{
    #[Test]
    public function it_calls_action(): void
    {
        $this->mock(UpdatesStock::class, function (MockInterface $mock): void {
            $mock->shouldReceive('update')->once();
        });

        /** @var Stock $model */
        $model = Stock::query()->create([
            'sku' => '::sku::',
            'update' => true,
        ]);

        UpdateStockJob::dispatch($model);
    }

    #[Test]
    public function it_has_unique_id(): void
    {
        /** @var Stock $model */
        $model = Stock::query()->create([
            'sku' => '::sku::',
            'update' => true,
        ]);

        $job = new UpdateStockJob($model);

        $this->assertEquals($model->id, $job->uniqueId());
    }

    #[Test]
    public function it_has_tags(): void
    {
        /** @var Stock $model */
        $model = Stock::query()->create([
            'sku' => '::sku::',
            'update' => true,
        ]);

        $job = new UpdateStockJob($model);

        $this->assertEquals(['::sku::'], $job->tags());
    }
}

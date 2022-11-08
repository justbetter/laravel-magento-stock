<?php

namespace JustBetter\MagentoStock\Tests\Jobs;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use JustBetter\ErrorLogger\Models\Error;
use JustBetter\MagentoProducts\Contracts\ChecksMagentoExistence;
use JustBetter\MagentoStock\Contracts\UpdatesBackorders;
use JustBetter\MagentoStock\Contracts\UpdatesStock;
use JustBetter\MagentoStock\Exceptions\UpdateException;
use JustBetter\MagentoStock\Jobs\UpdateStockJob;
use JustBetter\MagentoStock\Models\MagentoStock;
use JustBetter\MagentoStock\Tests\TestCase;
use Mockery\MockInterface;

class UpdateStockJobTest extends TestCase
{
    public function test_it_calls_actions(): void
    {
        $this->mock(ChecksMagentoExistence::class, function (MockInterface $mock) {
            $mock->shouldReceive('exists')->once()->andReturnTrue();
        });

        $this->mock(UpdatesStock::class, function (MockInterface $mock) {
            $mock->shouldReceive('update')->once();
        });

        $this->mock(UpdatesBackorders::class, function (MockInterface $mock) {
            $mock->shouldReceive('update')->once();
        });

        MagentoStock::query()
            ->create([
                'sku' => '::sku::',
                'quantity' => 10,
                'backorders' => false,
                'in_stock' => true,
            ]);

        UpdateStockJob::dispatch('::sku::');
    }

    public function test_it_throws_exception_sku_not_found(): void
    {
        $this->mock(ChecksMagentoExistence::class, function (MockInterface $mock) {
            $mock->shouldReceive('exists')->andReturnTrue();
        });

        $this->expectException(ModelNotFoundException::class);

        UpdateStockJob::dispatch('::sku::');
    }

    public function test_tags_uniqueid(): void
    {
        $job = new UpdateStockJob('::sku::');

        $this->assertEquals('::sku::', $job->uniqueId());
        $this->assertEquals(['::sku::'], $job->tags());
    }

    public function test_failed(): void
    {
        $model = MagentoStock::query()->create(['sku' => '::sku::']);

        $job = new UpdateStockJob('::sku::');

        $job->failed(new UpdateException('123', '::error::', ['sku' => '::sku::']));

        /** @var Error $error */
        $error = $model->errors()->first();

        $this->assertNotNull($error);
        $this->assertTrue(str_contains($error->message ?? '', '::sku::'));
    }
}

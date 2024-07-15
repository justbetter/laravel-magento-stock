<?php

namespace JustBetter\MagentoStock\Tests\Commands\Update;

use JustBetter\MagentoStock\Commands\Update\UpdateAllStockCommand;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class UpdateAllStockCommandTest extends TestCase
{
    #[Test]
    public function it_sets_update(): void
    {
        /** @var Stock $model */
        $model = Stock::query()->create([
            'sku' => '::sku::',
            'update' => false,
        ]);

        $this->artisan(UpdateAllStockCommand::class);

        $model->refresh();

        $this->assertTrue($model->update);
    }
}

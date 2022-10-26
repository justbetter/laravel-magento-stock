<?php

namespace JustBetter\MagentoStock\Tests\Models;

use JustBetter\MagentoStock\Models\MagentoStock;
use JustBetter\MagentoStock\Tests\TestCase;
use Spatie\Activitylog\LogOptions;

class MagentoStockTest extends TestCase
{
    public function test_retrieve_scope(): void
    {
        MagentoStock::query()
            ->create(['sku' => '::sku_1::', 'sync' => true, 'retrieve' => true]);
        MagentoStock::query()
            ->create(['sku' => '::sku_2::', 'sync' => true, 'retrieve' => false]);

        $this->assertCount(1, MagentoStock::shouldRetrieve()->get());
    }

    public function test_update_scope(): void
    {
        MagentoStock::query()
            ->create(['sku' => '::sku_1::', 'sync' => true, 'update' => true]);
        MagentoStock::query()
            ->create(['sku' => '::sku_2::', 'sync' => true, 'update' => false]);

        $this->assertCount(1, MagentoStock::shouldUpdate()->get());
    }

    public function test_it_resets_double_state_update(): void
    {
        $model = MagentoStock::query()->create([
            'sku' => '::sku::',
            'retrieve' => false,
            'update' => false,
        ]);

        // Both true => turn off update
        $model->retrieve = true;
        $model->update = true;

        $model->save();

        $this->assertTrue($model->retrieve);
        $this->assertFalse($model->update);
    }

    public function test_it_resets_double_state_retrieve(): void
    {
        $model = MagentoStock::query()->create([
            'sku' => '::sku::',
            'retrieve' => false,
            'update' => false,
        ]);

        // Both true => turn off update
        $model->retrieve = true;
        $model->update = true;

        $model->save();

        $this->assertTrue($model->retrieve);
        $this->assertFalse($model->update);

        $model->update = true;

        $model->save();

        $this->assertFalse($model->retrieve);
        $this->assertTrue($model->update);
    }

    public function test_activity_log_msi(): void
    {
        config()->set('magento-stock.msi', true);

        $model = new MagentoStock();

        $options = $model->getActivitylogOptions();

        $this->assertTrue(is_a($options, LogOptions::class));
    }
}

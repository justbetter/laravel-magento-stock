<?php

namespace JustBetter\MagentoStock\Tests\Models;

use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Tests\Fakes\FakeMsiRepository;
use JustBetter\MagentoStock\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Activitylog\LogOptions;

class StockModelTest extends TestCase
{
    #[Test]
    public function it_can_register_failures(): void
    {
        /** @var Stock $model */
        $model = Stock::query()->create([
            'sku' => '::sku::',
            'update' => true,
        ]);

        $model->failed();

        $this->assertNotNull($model->last_failed);
        $this->assertEquals(1, $model->fail_count);
        $this->assertTrue($model->update);
    }

    #[Test]
    public function it_will_set_retrieve_update_too_many_failures(): void
    {
        /** @var Stock $model */
        $model = Stock::query()->create([
            'sku' => '::sku::',
            'fail_count' => 100,
            'retrieve' => true,
            'update' => true,
        ]);

        $model->failed();

        $this->assertEquals(101, $model->fail_count);
        $this->assertFalse($model->retrieve);
        $this->assertFalse($model->update);
    }

    #[Test]
    public function it_resets_double_state_update(): void
    {
        /** @var Stock $model */
        $model = Stock::query()->create([
            'sku' => '::sku::',
            'retrieve' => false,
            'update' => false,
        ]);

        $model->retrieve = true;
        $model->update = true;

        $model->save();

        $this->assertTrue($model->retrieve);
        $this->assertFalse($model->update);
    }

    #[Test]
    public function it_resets_double_state_retrieve(): void
    {
        /** @var Stock $model */
        $model = Stock::query()->create([
            'sku' => '::sku::',
            'retrieve' => false,
            'update' => false,
        ]);

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

    #[Test]
    public function activity_log_msi(): void
    {
        config()->set('magento-stock.repository', FakeMsiRepository::class);
        $model = new Stock;

        $options = $model->getActivitylogOptions();

        $this->assertTrue(is_a($options, LogOptions::class));
    }
}

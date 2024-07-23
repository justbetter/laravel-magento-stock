<?php

namespace JustBetter\MagentoStock\Tests\Data;

use Illuminate\Validation\ValidationException;
use JustBetter\MagentoStock\Data\StockData;
use JustBetter\MagentoStock\Tests\Fakes\FakeMsiRepository;
use JustBetter\MagentoStock\Tests\Fakes\FakeRepository;
use JustBetter\MagentoStock\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class StockDataTest extends TestCase
{
    #[Test]
    public function it_passes_simple_rules(): void
    {
        config()->set('magento-stock.repository', FakeRepository::class);

        StockData::of([
            'sku' => '::sku::',
            'in_stock' => true,
            'quantity' => 10,
        ]);

        $this->assertTrue(true, 'No exception thrown');
    }

    #[Test]
    public function it_fails_simple_rules(): void
    {
        config()->set('magento-stock.repository', FakeRepository::class);

        $this->expectException(ValidationException::class);

        StockData::of([
            'sku' => '::sku::',
            'quantity' => 'invalid type',
        ]);
    }

    #[Test]
    public function it_passes_msi_rules(): void
    {
        config()->set('magento-stock.repository', FakeMsiRepository::class);

        StockData::of([
            'sku' => '::sku::',
            'msi_quantity' => [
                'A' => 0,
                'B' => 10,
            ],
            'msi_status' => [
                'A' => false,
                'B' => true,
            ],
        ]);

        $this->assertTrue(true, 'No exception thrown');
    }

    #[Test]
    public function it_fails_msi_rules(): void
    {
        config()->set('magento-stock.repository', FakeMsiRepository::class);

        $this->expectException(ValidationException::class);

        StockData::of([
            'sku' => '::sku::',
            'msi_quantity' => 10,
            'msi_status' => [
                'A' => 'invalid_type',
            ],
        ]);
    }

    #[Test]
    public function it_calculates_checksum(): void
    {
        $data = StockData::of([
            'sku' => '::sku::',
            'in_stock' => true,
            'quantity' => 10,
        ]);

        $this->assertEquals('895786ff2cebbee5a27a2950e0001abf', $data->checksum());
    }

    #[Test]
    public function it_handles_array_operations(): void
    {
        $data = StockData::of([
            'sku' => '::sku::',
            'in_stock' => true,
            'quantity' => 10,
        ]);

        $data['quantity'] = 20;

        $this->assertEquals(20, $data['quantity']);
        unset($data['quantity']);

        $this->assertNull($data['quantity']);
    }
}

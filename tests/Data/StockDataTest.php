<?php

namespace JustBetter\MagentoStock\Tests\Data;

use JustBetter\MagentoStock\Data\StockData;
use JustBetter\MagentoStock\Tests\TestCase;

class StockDataTest extends TestCase
{
    public function test_msi_setters(): void
    {
        $data = StockData::make('::sku::');

        $data->setMsiQuantity('A', 10);
        $data->setMsiStatus('A', false);

        $this->assertEquals(['A' => 10], $data->msiQuantity);
        $this->assertEquals(['A' => false], $data->msiStatus);
    }

    /**
     * @dataProvider msiStockDataProvider
     */
    public function test_it_compares_msi_stock(StockData $a, StockData $b, bool $equals): void
    {
        config()->set('magento-stock.msi', true);

        $this->assertEquals(
            $equals,
            $a->equals($b)
        );
    }

    public function msiStockDataProvider(): array
    {
        return [
            'Unchanged' => [
                'a' => new StockData(
                    sku: '::sku::',
                    msiQuantity: [
                        'A' => 5,
                        'B' => 0,
                        'C' => 0,
                    ],
                    msiStatus: [
                        'A' => true,
                        'B' => true,
                        'C' => false,
                    ]
                ),
                'b' => new StockData(
                    sku: '::sku::',
                    msiQuantity: [
                        'A' => 5,
                        'B' => 0,
                        'C' => 0,
                    ],
                    msiStatus: [
                        'A' => true,
                        'B' => true,
                        'C' => false,
                    ]
                ),
                'equals' => true,
            ],
            'Quantity change' => [
                'a' => new StockData(
                    sku: '::sku::',
                    msiQuantity: [
                        'A' => 5,
                        'B' => 2,
                        'C' => 0,
                    ],
                    msiStatus: [
                        'A' => true,
                        'B' => true,
                        'C' => false,
                    ]
                ),
                'b' => new StockData(
                    sku: '::sku::',
                    msiQuantity: [
                        'A' => 5,
                        'B' => 0,
                        'C' => 0,
                    ],
                    msiStatus: [
                        'A' => true,
                        'B' => true,
                        'C' => false,
                    ]
                ),
                'equals' => false,
            ],
            'Status change' => [
                'a' => new StockData(
                    sku: '::sku::',
                    msiQuantity: [
                        'A' => 5,
                        'B' => 0,
                        'C' => 0,
                    ],
                    msiStatus: [
                        'A' => true,
                        'B' => false,
                        'C' => false,
                    ]
                ),
                'b' => new StockData(
                    sku: '::sku::',
                    msiQuantity: [
                        'A' => 5,
                        'B' => 0,
                        'C' => 0,
                    ],
                    msiStatus: [
                        'A' => true,
                        'B' => true,
                        'C' => false,
                    ]
                ),
                'equals' => false,
            ],
            'Count change' => [
                'a' => new StockData(
                    sku: '::sku::',
                    msiQuantity: [
                        'A' => 5,
                        'B' => 0,
                    ],
                    msiStatus: [
                        'A' => true,
                        'B' => true,
                    ]
                ),
                'b' => new StockData(
                    sku: '::sku::',
                    msiQuantity: [
                        'A' => 5,
                        'B' => 0,
                        'C' => 0,
                    ],
                    msiStatus: [
                        'A' => true,
                        'B' => true,
                        'C' => false,
                    ]
                ),
                'equals' => false,
            ],
        ];
    }
}

<?php

namespace JustBetter\MagentoStock\Tests\Retriever;

use JustBetter\MagentoStock\Data\StockData;
use JustBetter\MagentoStock\Retriever\DummySkuRetriever;
use JustBetter\MagentoStock\Retriever\DummyStockRetriever;
use JustBetter\MagentoStock\Tests\TestCase;

class DummyRetrieversTest extends TestCase
{
    public function test_it_retrieves_dummy_stock(): void
    {
        /** @var DummyStockRetriever $retriever */
        $retriever = app(DummyStockRetriever::class);

        /** @var StockData $retrievedStock */
        $retrievedStock = $retriever->retrieve('::sku::');

        $this->assertNotNull($retrievedStock);
        $this->assertEquals('::sku::', $retrievedStock->sku);
    }

    public function test_it_retrieves_dummy_skus(): void
    {
        /** @var DummySkuRetriever $retriever */
        $retriever = app(DummySkuRetriever::class);

        $retrievedSkus = $retriever->retrieveAll();
        $this->assertEquals(['::sku_1::', '::sku_2::'], $retrievedSkus->toArray());

        $retrievedSkus = $retriever->retrieveUpdated();
        $this->assertEquals(['::sku_1::'], $retrievedSkus->toArray());
    }
}

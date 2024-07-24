<?php

namespace JustBetter\MagentoStock\Actions\Comparison;

use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoProducts\Contracts\ChecksMagentoExistence;
use JustBetter\MagentoStock\Contracts\Comparison\ComparesSimpleStock;
use JustBetter\MagentoStock\Events\DifferenceDetectedEvent;
use JustBetter\MagentoStock\Models\Stock;

class CompareSimpleStock implements ComparesSimpleStock
{
    public function __construct(
        protected Magento $magento,
        protected ChecksMagentoExistence $checksMagentoExistence
    ) {}

    public function compare(Stock $stock): void
    {
        if (! $this->checksMagentoExistence->exists($stock->sku)) {
            return;
        }

        $product = $this->magento
            ->get('products/'.urlencode($stock->sku))
            ->throw();

        $stockItem = $product->json('extension_attributes.stock_item', []);

        if ($stockItem === null || $this->quantityEquals($stock, $stockItem)) {
            return;
        }

        activity()
            ->performedOn($stock)
            ->log('Detected quantity difference, Magento: '.$stockItem['qty'].'. Should be: '.$stock->quantity);

        $stock->update = true;
        $stock->save();

        event(new DifferenceDetectedEvent($stock));
    }

    protected function quantityEquals(Stock $stock, array $magentoStockItem): bool
    {
        $magentoQuantity = $magentoStockItem['qty'];
        $currentQuantity = $stock->quantity;

        return $magentoQuantity == $currentQuantity;
    }

    public static function bind(): void
    {
        app()->singleton(ComparesSimpleStock::class, static::class);
    }
}

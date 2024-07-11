<?php

namespace JustBetter\MagentoStock\Actions\Comparinson;

use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoProducts\Contracts\ChecksMagentoExistence;
use JustBetter\MagentoStock\Contracts\Comparinson\ComparesStock;
use JustBetter\MagentoStock\Events\DifferenceDetectedEvent;
use JustBetter\MagentoStock\Models\Stock;

class CompareSimpleStock implements ComparesStock
{
    public function __construct(
        protected Magento $magento,
        protected ChecksMagentoExistence $checksMagentoExistence
    ) {
    }

    public function compare(string $sku): void
    {
        if (! $this->checksMagentoExistence->exists($sku)) {
            return;
        }

        /** @var Stock $localStock */
        $localStock = Stock::query()
            ->where('sku', '=', $sku)
            ->firstOrFail();

        $product = $this->magento
            ->get('products/'.urlencode($sku))
            ->throw();

        $stockItem = $product->json('extension_attributes.stock_item', []);

        if ($stockItem === null || $this->quantityEquals($localStock, $stockItem)) {
            return;
        }

        activity()
            ->performedOn($localStock)
            ->log('Detected quantity difference, Magento: '.$stockItem['qty'].'. Should be: '.$localStock->quantity);

        $localStock->update = true;
        $localStock->save();

        event(new DifferenceDetectedEvent($localStock));
    }

    protected function quantityEquals(Stock $stock, array $magentoStockItem): bool
    {
        $magentoQuantity = $magentoStockItem['qty'];
        $currentQuantity = $stock->quantity;

        return $magentoQuantity == $currentQuantity;
    }

    public static function bind(): void
    {
        app()->singleton(ComparesStock::class, static::class);
    }
}

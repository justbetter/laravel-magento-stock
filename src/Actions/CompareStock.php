<?php

namespace JustBetter\MagentoStock\Actions;

use Exception;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoProducts\Contracts\ChecksMagentoExistence;
use JustBetter\MagentoStock\Contracts\ComparesStock;
use JustBetter\MagentoStock\Models\MagentoStock;

class CompareStock implements ComparesStock
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

        /** @var MagentoStock $stock */
        $stock = MagentoStock::query()
            ->where('sku', '=', $sku)
            ->firstOrFail();

        $product = $this->magento
            ->get("products/$sku")
            ->throw();

        $stockItem = $product->json()['extension_attributes']['stock_item'];

        if ($this->quantityEquals($stock, $stockItem)) {
            return;
        }

        activity()
            ->performedOn($stock)
            ->log('Detected quantity difference: Magento: '.$stockItem['qty'].'. Should be: '.$stock->quantity);

        $stock->update = true;
        $stock->save();
    }

    protected function quantityEquals(MagentoStock $stock, array $magentoStockItem): bool
    {
        if (config('magento-stock.msi')) {
            throw new Exception('Not implemented');
        }

        $magentoQuantity = $magentoStockItem['qty'];
        $currentQuantity = $stock->quantity;

        return $magentoQuantity == $currentQuantity;
    }
}

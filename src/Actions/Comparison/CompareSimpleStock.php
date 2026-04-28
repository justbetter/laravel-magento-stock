<?php

declare(strict_types=1);

namespace JustBetter\MagentoStock\Actions\Comparison;

use Illuminate\Support\Facades\DB;
use JustBetter\MagentoClient\Client\Magento;
use JustBetter\MagentoProducts\Contracts\ChecksMagentoExistence;
use JustBetter\MagentoStock\Contracts\Comparison\ComparesSimpleStock;
use JustBetter\MagentoStock\Events\DifferenceDetectedEvent;
use JustBetter\MagentoStock\Models\Stock;
use JustBetter\MagentoStock\Repositories\BaseRepository;

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

        $shouldCompareBackorders = BaseRepository::resolve()->backorders();

        $isEqual = $this->quantityEquals($stock, $stockItem) && ($shouldCompareBackorders ? $stock->backorders->value === $stockItem['backorders'] : true);

        if ($stockItem === null || $isEqual) {
            return;
        }

        activity()
            ->performedOn($stock)
            ->log('Detected quantity difference, Magento: '.$stockItem['qty'].'. Should be: '.$stock->quantity);

        DB::transaction(function () use ($stock): void {
            /** @var Stock $locked */
            $locked = Stock::query()->lockForUpdate()->findOrFail($stock->id);
            $locked->update = true;
            $locked->save();
        });

        event(new DifferenceDetectedEvent($stock));
    }

    protected function quantityEquals(Stock $stock, array $magentoStockItem): bool
    {
        return $magentoStockItem['qty'] == $stock->quantity;
    }

    public static function bind(): void
    {
        app()->singleton(ComparesSimpleStock::class, static::class);
    }
}

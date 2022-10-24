<?php

namespace JustBetter\MagentoStock\Data;

use Illuminate\Contracts\Support\Arrayable;
use JustBetter\MagentoStock\Models\MagentoStock;

final class StockData implements Arrayable
{
    public function __construct(
        public string $sku,

        public float $quantity = 0,
        public bool $inStock = false,
        public bool $backorders = false,

        public array $msiQuantity = [],
        public array $msiStatus = [],

        public array $data = [],
    ) {
    }

    public static function make(
        string $sku,

        float $quantity = 0,
        bool $inStock = false,
        bool $backorders = false,

        array $msiQuantity = [],
        array $msiStatus = [],

        array $data = [],
    ): static {
        return new static($sku, $quantity, $inStock, $backorders, $msiQuantity, $msiStatus, $data);
    }

    public static function fromModel(MagentoStock $stock): static
    {
        return new static(
            $stock->sku,

            $stock->quantity,
            $stock->in_stock,
            $stock->backorders,

            $stock->msi_stock ?? [],
            $stock->msi_status ?? [],
        );
    }

    public function toModel(): MagentoStock
    {
        /** @var MagentoStock $model */
        $model = MagentoStock::query()
            ->firstOrNew(
                [
                    'sku' => $this->sku,
                ]
            );

        $model->fill($this->toArray());

        return $model;
    }

    public function setQuantity(float $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function setMsiQuantities(array $quantities): static
    {
        $this->msiQuantity = $quantities;

        return $this;
    }

    public function setMsiStatusses(array $statuses): static
    {
        $this->msiStatus = $statuses;

        return $this;
    }

    public function setBackorders(bool $backorders): static
    {
        $this->backorders = $backorders;

        return $this;
    }

    public function setInStock(bool $inStock): static
    {
        $this->inStock = $inStock;

        return $this;
    }

    public function setMsiQuantity(string $source, int $stock): static
    {
        $this->msiQuantity[$source] = $stock;
        $this->msiStatus[$source] = $stock > 0;

        return $this;
    }

    public function setMsiStatus(string $source, bool $status): static
    {
        $this->msiStatus[$source] = $status;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'sku' => $this->sku,
            'in_stock' => $this->inStock,
            'backorders' => $this->backorders,
            'msi_stock' => $this->msiQuantity,
            'msi_status' => $this->msiStatus,
            'quantity' => $this->quantity,
        ];
    }

    public function equals(StockData $other): bool
    {
        /** @var string $a */
        $a = json_encode($this->toArray());
        /** @var string $b */
        $b = json_encode($other->toArray());

        return md5($a) == md5($b);
    }
}

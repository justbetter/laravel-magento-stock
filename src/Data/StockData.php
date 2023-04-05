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
        return new self($sku, $quantity, $inStock, $backorders, $msiQuantity, $msiStatus, $data);
    }

    public static function fromModel(MagentoStock $stock): static
    {
        return new self(
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
        if ($this->sku !== $other->sku || $this->inStock !== $other->inStock || $this->backorders !== $other->backorders) {
            return false;
        }

        if (config('magento-stock.msi', false) === false) {
            return $this->quantity === $other->quantity;
        }

        if (
            count($this->msiQuantity) !== count($other->msiQuantity) ||
            count($this->msiStatus) !== count($other->msiStatus)
        ) {
            return false;
        }

        foreach ($this->msiQuantity as $source => $quantity) {
            $matchingQuantity = $other->msiQuantity[$source] ?? null;

            if ($matchingQuantity === null || $quantity !== $matchingQuantity) {
                return false;
            }
        }

        foreach ($this->msiStatus as $source => $status) {
            $matchingStatus = $other->msiStatus[$source] ?? null;

            if ($matchingStatus === null || $status !== $matchingStatus) {
                return false;
            }
        }

        return true;
    }
}

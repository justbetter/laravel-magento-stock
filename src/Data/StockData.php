<?php

namespace JustBetter\MagentoStock\Data;

use Illuminate\Validation\Rule;
use JustBetter\MagentoStock\Enums\Backorders;
use JustBetter\MagentoStock\Repositories\BaseRepository;

/**
 * @property string $sku
 * @property int $quantity
 * @property ?Backorders $backorders
 * @property array $msi_quantity
 * @property array $msi_status
 */
class StockData extends Data
{
    public function getRules(): array
    {
        $repository = BaseRepository::resolve();

        if ($repository->msi()) {
            return [
                'sku' => ['required'],
                'backorders' => ['nullable', Rule::enum(Backorders::class)],

                'msi_quantity' => ['required', 'array'],
                'msi_quantity.*' => ['integer'],

                'msi_status' => ['required', 'array'],
                'msi_status.*' => ['boolean'],
            ];
        }

        return [
            'sku' => ['required'],
            'quantity' => ['required', 'numeric'],
            'backorders' => ['nullable', Rule::enum(Backorders::class)],
        ];
    }
}

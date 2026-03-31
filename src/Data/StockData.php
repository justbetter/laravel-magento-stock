<?php

declare(strict_types=1);

namespace JustBetter\MagentoStock\Data;

use Illuminate\Validation\Rule;
use JustBetter\MagentoStock\Enums\Backorders;
use JustBetter\MagentoStock\Repositories\BaseRepository;

class StockData extends Data
{
    #[\Override]
    public function rules(): array
    {
        $repository = BaseRepository::resolve();

        $rules = [
            'sku' => ['required'],
            'backorders' => ['nullable', Rule::enum(Backorders::class)],
        ];

        if ($repository->msi()) {
            return array_merge($rules, [
                'msi_quantity' => ['required', 'array'],
                'msi_quantity.*' => ['numeric'],

                'msi_status' => ['required', 'array'],
                'msi_status.*' => ['boolean'],
            ]);
        }

        return array_merge($rules, [
            'in_stock' => ['required', 'boolean'],
            'quantity' => ['required', 'numeric'],
        ]);
    }

    public function checksum(): string
    {
        $json = json_encode($this->validated());

        throw_if($json === false, 'Failed to generate checksum');

        return md5($json);
    }
}

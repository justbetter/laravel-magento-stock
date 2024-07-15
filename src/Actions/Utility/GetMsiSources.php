<?php

namespace JustBetter\MagentoStock\Actions\Utility;

use JustBetter\MagentoClient\Client\Magento;

class GetMsiSources
{
    public function __construct(protected Magento $magento) {}

    public function get(): array
    {
        return cache()->remember(
            'magento-stock:update-msi:sources',
            now()->addDay(),
            fn (): array => $this->magento
                ->lazy('inventory/sources')
                ->pluck('source_code')
                ->toArray()
        );
    }
}

<?php

declare(strict_types=1);

namespace JustBetter\MagentoStock\Contracts;

interface ProcessesStocks
{
    public function process(): void;
}

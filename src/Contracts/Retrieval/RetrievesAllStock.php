<?php

declare(strict_types=1);

namespace JustBetter\MagentoStock\Contracts\Retrieval;

use Illuminate\Support\Carbon;

interface RetrievesAllStock
{
    public function retrieve(?Carbon $from, bool $defer = true): void;
}

<?php

namespace JustBetter\MagentoStock\Contracts\Retrieval;

use Illuminate\Support\Carbon;

interface RetrievesAllStock
{
    public function retrieve(?Carbon $from): void;
}

<?php

namespace JustBetter\MagentoStock\Exceptions;

use Exception;
use Throwable;

class UpdateException extends Exception
{
    public function __construct(string $sku, string $message, public array $payload = [], Throwable $previous = null)
    {
        parent::__construct("Failed to update $sku: $message", 0, $previous);
    }
}

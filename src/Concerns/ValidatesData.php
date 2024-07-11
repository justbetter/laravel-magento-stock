<?php

namespace JustBetter\MagentoStock\Concerns;

use Illuminate\Support\Facades\Validator;

trait ValidatesData
{
    public array $rules = [];

    public function validate(array $data): void
    {
        Validator::make($data, $this->rules)->validate();
    }

    public function validated(): array
    {
        return Validator::make($this->toArray(), $this->rules)->validated();
    }
}

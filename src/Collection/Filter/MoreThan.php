<?php

namespace Kirby\Collection\Filter;

use Kirby\Collection\Filter;

class MoreThan extends Filter
{
    public function filter($value, $input): bool
    {
        return $value > $input;
    }
}

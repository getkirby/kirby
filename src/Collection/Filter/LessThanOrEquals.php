<?php

namespace Kirby\Collection\Filter;

use Kirby\Collection\Filter;

class LessThanOrEquals extends Filter
{
    public function filter($value, $input): bool
    {
        return $value <= $input;
    }
}

<?php

namespace Kirby\Collection\Filter;

use Kirby\Collection\Filter;

class Between extends Filter
{
    public function filter($value, $input): bool
    {
        return $value >= $input[0] && $value <= $input[1];
    }
}

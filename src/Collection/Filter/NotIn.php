<?php

namespace Kirby\Collection\Filter;

use Kirby\Collection\Filter;

class NotIn extends Filter
{
    public function filter($value, $input): bool
    {
        return in_array($value, $input) === false;
    }
}

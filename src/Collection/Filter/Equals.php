<?php

namespace Kirby\Collection\Filter;

use Kirby\Collection\Filter;

class Equals extends Filter
{
    public function filter($value, $input): bool
    {
        return $value == $input;
    }
}

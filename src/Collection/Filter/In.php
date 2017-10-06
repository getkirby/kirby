<?php

namespace Kirby\Collection\Filter;

use Kirby\Collection\Filter;

class In extends Filter
{
    public function filter($value, $input): bool
    {
        return in_array($value, $input);
    }
}

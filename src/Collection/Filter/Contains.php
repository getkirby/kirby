<?php

namespace Kirby\Collection\Filter;

use Kirby\Collection\Filter;
use Kirby\Toolkit\Str;

class Contains extends Filter
{
    public function filter($value, $input): bool
    {
        return Str::contains($value, $input);
    }
}

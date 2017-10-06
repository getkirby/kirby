<?php

namespace Kirby\Collection\Filter;

use Kirby\Collection\Filter;
use Kirby\Toolkit\Str;

class EndsWith extends Filter
{
    public function filter($value, $input): bool
    {
        return Str::endsWith($value, $input);
    }
}

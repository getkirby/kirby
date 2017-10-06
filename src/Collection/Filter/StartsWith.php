<?php

namespace Kirby\Collection\Filter;

use Kirby\Collection\Filter;
use Kirby\Toolkit\Str;

class StartsWith extends Filter
{
    public function filter($value, $input): bool
    {
        return Str::startsWith($value, $input);
    }
}

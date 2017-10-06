<?php

namespace Kirby\Collection;

abstract class Filter
{
    abstract public function filter($value, $input): bool;
}

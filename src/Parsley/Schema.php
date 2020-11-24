<?php

namespace Kirby\Parsley;

abstract class Schema
{
    abstract public function fallback(string $html);
    abstract public function marks(): array;
    abstract public function nodes(): array;
    abstract public function skip(): array;
}

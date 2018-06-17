<?php

namespace Kirby\Cms;

use Closure;

/**
 * Specific Collection extension for all Blueprint objects
 *
 * TODO: refactor this to use the Kirby\Cms\Collection instead
 * We don't really need this
 */
class BlueprintCollection extends Collection
{
    protected static $accept = BlueprintObject::class;

    public function __debuginfo(): array
    {
        return $this->toArray();
    }

    public function toArray(Closure $map = null): array
    {
        return array_values(parent::toArray($map));
    }
}

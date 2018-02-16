<?php

namespace Kirby\Cms;

use Closure;

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

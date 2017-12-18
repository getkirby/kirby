<?php

namespace Kirby\Cms;

use Closure;

class BlueprintCollection extends Collection
{

    public function __debuginfo(): array
    {
        return $this->toArray();
    }

    public function toArray(Closure $map = null): array
    {
        return array_values(parent::toArray($map));
    }

    public function toLayout(): array
    {
        return array_values($this->toArray(function($object) {
            return $object->toLayout();
        }));
    }

}

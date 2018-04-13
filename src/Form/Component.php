<?php

namespace Kirby\Form;

use Kirby\Util\Properties;

abstract class Component
{
    use Properties;

    public function __construct(array $props = [])
    {
        $this->setProperties($props);
    }

    public function toArray(): array
    {
        $array = $this->propertiesToArray();

        // keep it tidy
        ksort($array);

        return $array;
    }
}

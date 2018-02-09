<?php

namespace Kirby\Cms;

use Closure;

class Locales extends Collection
{

    protected static $accept = Locale::class;

    public function __debuginfo(): array
    {
        return $this->toArray();
    }

    public function __set(string $id, $object)
    {
        if (is_a($object, static::$accept) === false) {
            throw new Exception(sprintf('Invalid object in collection. Accepted: "%s"', static::$accept));
        }

        return $this->data[$object->id()] = $object;
    }

}

<?php

namespace Kirby\Cms;

use Kirby\Exception\InvalidArgumentException;

class Translations extends Collection
{

    protected static $accept = Translation::class;

    public function __debuginfo(): array
    {
        return $this->toArray();
    }

    public function __set(string $id, $object)
    {
        if (is_a($object, static::$accept) === false) {
            throw new InvalidArgumentException(sprintf('Invalid object in collection. Accepted: "%s"', static::$accept));
        }

        return $this->data[$object->id()] = $object;
    }

}

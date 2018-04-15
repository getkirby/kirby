<?php

namespace Kirby\Collection\Traits;

use Closure;

trait Converter
{

    /**
     * Converts the current object into an array
     *
     * @return array
     */
    public function toArray(Closure $map = null): array
    {
        if ($map !== null) {
            return array_map($map, $this->data);
        }

        return $this->data;
    }

    /**
     * Converts the current object into a json string
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Convertes the collection to a string
     *
     * @return string
     */
    public function toString(): string
    {
        return implode('<br />', $this->keys());
    }

    /**
     * Makes it possible to echo the entire object
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}

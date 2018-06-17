<?php

namespace Kirby\Toolkit;

use stdClass;

/**
 * Super simple stdClass extension with
 * magic getter methods for all properties
 */
class Obj extends stdClass
{

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $val) {
            $this->$key = $val;
        }
    }

    /**
     * Magic getter
     *
     * @param string $property
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $property, array $arguments)
    {
        return $this->$property ?? null;
    }

    /**
     * Magic property getter
     *
     * @param string $property
     * @return mixed
     */
    public function __get(string $property)
    {
        return null;
    }

    /**
     * Converts the object to an array
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [];

        foreach ((array)$this as $key => $value) {
            if (is_object($value) === true && method_exists($value, 'toArray')) {
                $result[$key] = $value->toArray();
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Converts the object to a json string
     *
     * @return string
     */
    public function toJson(...$arguments): string
    {
        return json_encode($this->toArray(), ...$arguments);
    }

    /**
     * Improved var_dump() output
     *
     * @return array
     */
    public function __debuginfo(): array
    {
        return $this->toArray();
    }
}

<?php

namespace Kirby\Util;

use stdClass;

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
            if (is_string($key) === false || Str::length($key) === 0) {
                continue;
            }

            $this->{strtolower($key)} = $val;
        }
    }

    /**
     * Magic getter
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        return $this->{strtolower($method)} ?? null;
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
     * Improved var_dump() output
     *
     * @return array
     */
    public function __debuginfo(): array
    {
        return $this->toArray();
    }
}

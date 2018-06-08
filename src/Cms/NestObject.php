<?php

namespace Kirby\Cms;

use Kirby\Toolkit\Obj;

class NestObject extends Obj
{

    /**
     * Converts the object to an array
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [];

        foreach ((array)$this as $key => $value) {
            if (is_a($value, Field::class) === true) {
                $result[$key] = $value->value();
                continue;
            }

            if (is_object($value) === true && method_exists($value, 'toArray')) {
                $result[$key] = $value->toArray();
                continue;
            }

            $result[$key] = $value;
        }

        return $result;
    }
}

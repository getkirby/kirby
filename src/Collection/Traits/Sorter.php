<?php

namespace Kirby\Collection\Traits;

use Kirby\Toolkit\Str;

trait Sorter
{

    /**
     * Sorts the object by any number of fields
     *
     * @param   $field      string
     * @param   $direction  string  asc or desc
     * @param   $method     int     The sort flag, SORT_REGULAR, SORT_NUMERIC etc.
     * @return  SortGroup
     */
    public function sortBy(): self
    {
        if (empty($this->data) === true) {
            return $this;
        }

        $args       = func_get_args();
        $array      = $this->data;
        $collection = $this->clone();
        $params     = [];

        foreach ($args as $i => $param) {
            if (is_string($param) === true) {
                if (strtolower($param) === 'desc') {
                    ${"param_$i"} = SORT_DESC;
                } elseif (strtolower($param) === 'asc') {
                    ${"param_$i"} = SORT_ASC;
                } else {
                    ${"param_$i"} = [];
                    foreach ($array as $index => $row) {
                        ${"param_$i"}[$index] = Str::lower($collection->getAttribute($row, $param));
                    }
                }
            } else {
                ${"param_$i"} = $args[$i];
            }
            $params[] = &${"param_$i"};
        }

        $params[] = &$array;

        array_multisort(...$params);

        return $collection->data($array);
    }

    /**
     * Returns the array in reverse order
     *
     * @return Collection
     */
    public function flip(): self
    {
        return $this->clone()->data(array_reverse($this->data, true));
    }

    /**
     * Shuffle all elements in the array
     *
     * @return Collection
     */
    public function shuffle(): self
    {
        $data = $this->data;
        $keys = $this->keys();
        shuffle($keys);

        $collection = $this->empty();

        foreach ($keys as $key) {
            $collection->set($key, $data[$key]);
        }

        return $collection;
    }

}

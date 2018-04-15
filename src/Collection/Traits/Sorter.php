<?php

namespace Kirby\Collection\Traits;

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
        // there is no need to sort empty collections
        if (empty($this->data) === true) {
            return $this;
        }

        $args       = func_get_args();
        $array      = $this->data;
        $collection = $this->clone();

        // loop through all method arguments and find sets of fields to sort by
        $fields = [];
        foreach ($args as $arg) {
            // get the index of the latest field array inside the $fields array
            $currentField = ($fields)? count($fields) - 1 : 0;

            // detect the type of argument
            // sorting direction
            $argLower = strtolower($arg);
            if ($arg === SORT_ASC || $argLower === 'asc') {
                $fields[$currentField]['direction'] = SORT_ASC;
            } elseif ($arg === SORT_DESC || $argLower === 'desc') {
                $fields[$currentField]['direction'] = SORT_DESC;

            // other string: The field name
            } elseif (is_string($arg)) {
                $values = $collection->toArray(function ($value) use ($collection, $arg) {
                    $value = $collection->getAttribute($value, $arg);

                    // make sure that we return something sortable
                    // but don't convert other scalars (especially numbers) to strings!
                    if (is_scalar($value)) {
                        return $value;
                    } else {
                        return (string)$value;
                    }
                });

                $fields[] = ['field' => $arg, 'values' => $values];

            // flags
            } else {
                $fields[$currentField]['flags'] = $arg;
            }
        }

        // build the multisort params in the right order
        $params = [];
        foreach ($fields as $field) {
            $params[] = $field['values']    ?? [];
            $params[] = $field['direction'] ?? SORT_ASC;
            $params[] = $field['flags']     ?? SORT_REGULAR;
        }
        $params[] = &$array;

        // array_multisort receives $params as separate params
        array_multisort(...$params);

        // $array has been overwritten by array_multisort
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

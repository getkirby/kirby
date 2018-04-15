<?php

namespace Kirby\Collection\Traits;

use Closure;
use Exception;

use Kirby\Toolkit\V;

trait Filter
{
    protected $filters = [
        'between'   => 'between',
        '*='        => 'contains',
        '!*='       => 'notContains',
        '$='        => 'endsWith',
        '=='        => 'same',
        'in'        => 'in',
        '<'         => 'less',
        '<='        => 'max',
        '>'         => 'more',
        '>='        => 'min',
        '!='        => 'different',
        'not in'    => 'notIn',
        '^='        => 'startsWith',
        'match'     => 'match',
        'maxLength' => 'maxLength',
        'minLength' => 'minLength',
        'maxWords'  => 'maxWords',
        'minWords'  => 'minWords',
    ];

    public function filter($filter): self
    {
        if (is_array($filter)) {
            $collection = $this;

            foreach ($filter as $arguments) {
                $collection = $collection->filterBy(...$arguments);
            }

            return $collection;
        } elseif (is_a($filter, 'Closure')) {
            return $this->clone()->data(array_filter($this->data, $filter));
        }

        throw new Exception('The filter method needs either an array of filterBy rules or a closure function to be passed as parameter.');
    }

    public function filterBy(string $attribute, $operator, ...$filter): self
    {
        if (count(func_get_args()) === 2) {
            $filter   = [$operator];
            $operator = '==';
        }

        if (!isset($this->filters[$operator])) {
            throw new Exception('Missing filter class for operator: ' . $operator);
        }

        $filterMethod = $this->filters[$operator];
        $collection  = $this->clone();

        foreach ($this->data as $key => $item) {
            if (V::$filterMethod($this->getAttribute($item, $attribute), ...$filter) !== true) {
                $collection->remove($key);
            }
        }

        return $collection;
    }

    /**
     * Returns a Collection without the given element(s)
     *
     * @param  args    any number of keys, passed as individual arguments
     * @return Collection
     */
    public function not(...$keys)
    {
        $collection = $this->clone();
        foreach ($keys as $key) {
            unset($collection->$key);
        }
        return $collection;
    }
}

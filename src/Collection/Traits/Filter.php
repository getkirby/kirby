<?php

namespace Kirby\Collection\Traits;

use Closure;

use Kirby\Collection\Filter\Between;
use Kirby\Collection\Filter\Contains;
use Kirby\Collection\Filter\Custom;
use Kirby\Collection\Filter\EndsWith;
use Kirby\Collection\Filter\Equals;
use Kirby\Collection\Filter\In;
use Kirby\Collection\Filter\LessThan;
use Kirby\Collection\Filter\LessThanOrEquals;
use Kirby\Collection\Filter\MoreThan;
use Kirby\Collection\Filter\MoreThanOrEquals;
use Kirby\Collection\Filter\NotEquals;
use Kirby\Collection\Filter\NotInt;
use Kirby\Collection\Filter\StartsWith;

trait Filter
{

    protected $filters = [
        'between' => Between::class,
        '*='      => Contains::class,
        '$='      => EndsWith::class,
        '=='      => Equals::class,
        'in'      => In::class,
        '<'       => LessThan::class,
        '<='      => LessThanOrEquals::class,
        '>'       => MoreThan::class,
        '>='      => MoreThanOrEquals::class,
        '!='      => NotEquals::class,
        'not in'  => NotInt::class,
        '^='      => StartsWith::class
    ];

    public function filter($filter): self
    {
        if (is_array($filter)) {

            $collection = $this;

            foreach ($filter as $arguments) {
                $collection = $collection->filterBy(...$arguments);
            }

            return $collection;

        } else if (is_a($filter, 'Closure')) {
            return $this->clone()->data(array_filter($this->data, $filter));
        }
    }

    public function filterBy(string $attribute, $operator, $value = null): self
    {

        if (count(func_get_args()) === 2) {
            $value    = $operator;
            $operator = '==';
        }

        if (!isset($this->filters[$operator])) {
            throw new Exception('Missing filter class for operator: ' . $operator);
        }

        $filterClass = $this->filters[$operator];
        $filter      = new $filterClass;
        $collection  = $this->clone();

        foreach ($this->data as $key => $item) {
            if ($filter->filter($this->getAttribute($item, $attribute), $value) !== true) {
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

<?php

namespace Kirby\Toolkit;

use Closure;
use Countable;
use Exception;

/**
 * The collection class provides a nicer
 * interface around arrays of arrays or objects,
 * with advanced filters, sorting, navigation and more.
 */
class Collection extends Iterator implements Countable
{

    /**
     * All registered collection filters
     *
     * @var array
     */
    public static $filters = [];

    /**
     * Pagination object
     * @var Pagination
     */
    protected $pagination;

    /**
     * Magic getter function
     *
     * @param  string $key
     * @param  mixed  $arguments
     * @return mixed
     */
    public function __call(string $key, $arguments)
    {
        return $this->__get($key);
    }

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->set($data);
    }

    /**
     * Improve var_dump() output
     *
     * @return array
     */
    public function __debuginfo(): array
    {
        return $this->keys();
    }

    /**
     * Low-level getter for elements
     *
     * @param  mixed $key
     * @return mixed
     */
    public function __get($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }

        return $this->data[strtolower($key)] ?? null;
    }

    /**
     * Low-level setter for elements
     *
     * @param string  $key    string or array
     * @param mixed   $value
     */
    public function __set(string $key, $value)
    {
        $this->data[strtolower($key)] = $value;
        return $this;
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

    /**
     * Low-level element remover
     *
     * @param mixed $key the name of the key
     */
    public function __unset($key)
    {
        unset($this->data[$key]);
    }

    /**
     * Appends an element
     *
     * @param  mixed      $key
     * @param  mixed      $item
     * @return Collection
     */
    public function append(...$args)
    {
        if (count($args) === 1) {
            $this->data[] = $args[0];
        } elseif (count($args) === 2) {
            $this->set($args[0], $args[1]);
        }

        return $this;
    }

    /**
     * Creates chunks of the same size.
     * The last chunk may be smaller
     *
     * @param  int        $size  Number of elements per chunk
     * @return Collection        A new collection with an element for each chunk and
     *                           a sub collection in each chunk
     */
    public function chunk(int $size)
    {
        // create a multidimensional array that is chunked with the given
        // chunk size keep keys of the elements
        $chunks = array_chunk($this->data, $size, true);

        // convert each chunk to a subcollection
        $collection = [];

        foreach ($chunks as $items) {
            // we clone $this instead of creating a new object because
            // different objects may have different constructors
            $clone = clone $this;
            $clone->data = $items;

            $collection[] = $clone;
        }

        // convert the array of chunks to a collection
        $result = clone $this;
        $result->data = $collection;

        return $result;
    }

    /**
     * Returns a cloned instance of the collection
     *
     * @return self
     */
    public function clone(): self
    {
        return clone $this;
    }

    /**
     * Getter and setter for the data
     *
     * @param  array $data
     * @return array|Collection
     */
    public function data(array $data = null)
    {
        if ($data === null) {
            return $this->data;
        }

        // clear all previous data
        $this->data = [];

        // overwrite the data array
        $this->data = $data;

        return $this;
    }

    /**
     * Clone and remove all elements from the collection
     *
     * @return Collection
     */
    public function empty()
    {
        $collection = clone $this;
        $collection->data = [];

        return $collection;
    }

    /**
     * Adds all elements to the collection
     *
     * @return Collection
     */
    public function extend($items): self
    {
        $collection = clone $this;
        return $collection->set($items);
    }

    /**
     * Filters elements by a custom
     * filter function or an array of filters
     *
     * @param Closure $filter
     * @return self
     */
    public function filter($filter)
    {
        if (is_callable($filter) === true) {
            $collection = clone $this;
            $collection->data = array_filter($this->data, $filter);

            return $collection;
        } elseif (is_array($filter) === true) {
            $collection = $this;

            foreach ($filter as $arguments) {
                $collection = $collection->filterBy(...$arguments);
            }

            return $collection;
        }

        throw new Exception('The filter method needs either an array of filterBy rules or a closure function to be passed as parameter.');
    }

    /**
     * Filters elements by one of the
     * predefined filter methods.
     *
     * @param string $field
     * @return self
     */
    public function filterBy(string $field, ...$args)
    {
        $operator = '==';
        $test     = $args[0] ?? null;
        $split    = $args[1] ?? false;

        if (is_string($test) === true && isset(static::$filters[$test]) === true) {
            $operator = $test;
            $test     = $args[1] ?? null;
            $split    = $args[2] ?? false;
        }

        if (is_object($test) === true && method_exists($test, '__toString') === true) {
            $test = (string)$test;
        }

        // get the filter from the filters array
        $filter = static::$filters[$operator] ?? null;

        // return an unfiltered list if the filter does not exist
        if ($filter === null) {
            return $this;
        }

        if (is_array($filter) === true) {
            $collection = clone $this;
            $validator  = $filter['validator'];
            $strict     = $filter['strict'] ?? true;
            $method     = $strict ? 'filterMatchesAll' : 'filterMatchesAny';

            foreach ($collection->data as $key => $item) {
                $value = $collection->getAttribute($item, $field, $split);

                if ($split !== false) {
                    if ($this->$method($validator, $value, $test) === false) {
                        unset($collection->data[$key]);
                    }
                } elseif ($validator($value, $test) === false) {
                    unset($collection->data[$key]);
                }
            }

            return $collection;
        }

        return $filter(clone $this, $field, $test, $split);
    }

    protected function filterMatchesAny($validator, $values, $test): bool
    {
        foreach ($values as $value) {
            if ($validator($value, $test) !== false) {
                return true;
            }
        }

        return false;
    }

    protected function filterMatchesAll($validator, $values, $test): bool
    {
        foreach ($values as $value) {
            if ($validator($value, $test) === false) {
                return false;
            }
        }

        return true;
    }

    protected function filterMatchesNone($validator, $values, $test): bool
    {
        $matches = 0;

        foreach ($values as $value) {
            if ($validator($value, $test) !== false) {
                $matches++;
            }
        }

        return $matches === 0;
    }

    /**
     * Find one or multiple elements by id
     *
     * @param string ...$keys
     * @return mixed
     */
    public function find(...$keys)
    {
        if (count($keys) === 1) {
            if (is_array($keys[0]) === true) {
                $keys = $keys[0];
            } else {
                return $this->findByKey($keys[0]);
            }
        }

        $result = [];

        foreach ($keys as $key) {
            if ($item = $this->findByKey($key)) {
                if (is_object($item) && method_exists($item, 'id') === true) {
                    $key = $item->id();
                }
                $result[$key] = $item;
            }
        }

        $collection = clone $this;
        $collection->data = $result;
        return $collection;
    }

    /**
     * Find a single element by an attribute and its value
     *
     * @param string $attribute
     * @param mixed $value
     * @return mixed
     */
    public function findBy(string $attribute, $value)
    {
        foreach ($this->data as $key => $item) {
            if ($this->getAttribute($item, $attribute) == $value) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Find a single element by key (id)
     *
     * @param string $key
     * @return mixed
     */
    public function findByKey($key)
    {
        return $this->get($key);
    }

    /**
     * Returns the first element
     *
     * @return mixed
     */
    public function first()
    {
        $array = $this->data;
        return array_shift($array);
    }

    /**
     * Returns the elements in reverse order
     *
     * @return Collection
     */
    public function flip()
    {
        $collection = clone $this;
        $collection->data = array_reverse($this->data, true);
        return $collection;
    }

    /**
     * Getter
     *
     * @param  mixed  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->__get($key) ?? $default;
    }

    /**
     * Extracts an attribute value from the given element
     * in the collection. This is useful if elements in the collection
     * might be objects, arrays or anything else and you need to
     * get the value independently from that. We use it for filterBy.
     *
     * @param array|object $item
     * @param string $attribute
     * @param boolean $split
     * @param mixed $related
     * @return mixed
     */
    public function getAttribute($item, string $attribute, $split = false, $related = null)
    {
        $value = $this->{'getAttributeFrom' . gettype($item)}($item, $attribute);

        if ($split !== false) {
            return Str::split($value, $split === true ? ',' : $split);
        }

        if ($related !== null) {
            return Str::toType((string)$value, $related);
        }

        return $value;
    }

    /**
     * @param array $array
     * @param string $attribute
     * @return mixed
     */
    protected function getAttributeFromArray(array $array, string $attribute)
    {
        return $array[$attribute] ?? null;
    }

    /**
     * @param object $object
     * @param string $attribute
     * @return void
     */
    protected function getAttributeFromObject($object, string $attribute)
    {
        return $object->{$attribute}();
    }

    /**
     * Groups the elements by a given callback
     *
     * @param Closure $callback
     * @return Collection A new collection with an element for each group and a subcollection in each group
     */
    public function group(Closure $callback): Collection
    {
        $groups = [];

        foreach ($this->data as $key => $item) {

            // get the value to group by
            $value = $callback($item);

            // make sure that there's always a proper value to group by
            if (!$value) {
                throw new Exception('Invalid grouping value for key: ' . $key);
            }

            // make sure we have a proper key for each group
            if (is_array($value) === true) {
                throw new Exception('You cannot group by arrays or objects');
            } elseif (is_object($value) === true) {
                if (method_exists($value, '__toString') === false) {
                    throw new Exception('You cannot group by arrays or objects');
                } else {
                    $value = (string)$value;
                }
            }

            if (isset($groups[$value]) === false) {
                // create a new entry for the group if it does not exist yet
                $groups[$value] = new static([$key => $item]);
            } else {
                // add the element to an existing group
                $groups[$value]->set($key, $item);
            }
        }

        return new Collection($groups);
    }

    /**
     * Groups the elements by a given field
     *
     * @param string $field
     * @param bool $i
     * @return Collection A new collection with an element for each group and a subcollection in each group
     */
    public function groupBy($field, bool $i = true)
    {
        if (is_string($field) === false) {
            throw new Exception('Cannot group by non-string values. Did you mean to call group()?');
        }

        return $this->group(function ($item) use ($field, $i) {
            $value = $this->getAttribute($item, $field);

            // ignore upper/lowercase for group names
            return $i === true ? Str::lower($value) : $value;
        });
    }

    /**
     * Checks if the number of elements is zero
     *
     * @return boolean
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * Checks if the number of elements is even
     *
     * @return boolean
     */
    public function isEven(): bool
    {
        return $this->count() % 2 === 0;
    }

    /**
     * Checks if the number of elements is more than zero
     *
     * @return boolean
     */
    public function isNotEmpty(): bool
    {
        return $this->count() > 0;
    }

    /**
     * Checks if the number of elements is odd
     *
     * @return boolean
     */
    public function isOdd(): bool
    {
        return $this->count() % 2 !== 0;
    }

    /**
     * Returns the last element
     *
     * @return mixed
     */
    public function last()
    {
        $array = $this->data;
        return array_pop($array);
    }

    /**
     * Returns a new object with a limited number of elements
     *
     * @param  int        $limit  The number of elements to return
     * @return Collection
     */
    public function limit(int $limit)
    {
        return $this->slice(0, $limit);
    }

    /**
     * Map a function to each element
     *
     * @param  callable $callback
     * @return Collection
     */
    public function map(callable $callback)
    {
        $this->data = array_map($callback, $this->data);
        return $this;
    }

    /**
     * Returns the nth element from the collection
     *
     * @param integer $n
     * @return mixed
     */
    public function nth(int $n)
    {
        return array_values($this->data)[$n] ?? null;
    }

    /**
     * Returns a Collection without the given element(s)
     *
     * @param  args    any number of keys, passed as individual arguments
     * @return Collection
     */
    public function not(...$keys)
    {
        $collection = clone $this;
        foreach ($keys as $key) {
            unset($collection->data[$key]);
        }
        return $collection;
    }

    /**
     * Returns a new object starting from the given offset
     *
     * @param  int        $offset  The index to start from
     * @return Collection
     */
    public function offset(int $offset)
    {
        return $this->slice($offset);
    }

    /**
     * Add pagination
     *
     * @return Collection a sliced set of data
     */
    public function paginate(...$arguments)
    {
        $this->pagination = Pagination::for($this, ...$arguments);

        // slice and clone the collection according to the pagination
        return $this->slice($this->pagination->offset(), $this->pagination->limit());
    }

    /**
     * Get the previously added pagination object
     *
     * @return Pagination|null
     */
    public function pagination()
    {
        return $this->pagination;
    }

    /**
     * Extracts all values for a single field into
     * a new array
     *
     * @param string $field
     * @param string $split
     * @param bool $unique
     * @return array
     */
    public function pluck(string $field, string $split = null, bool $unique = false): array
    {
        $result = [];

        foreach ($this->data as $item) {
            $row = $this->getAttribute($item, $field);

            if ($split !== null) {
                $result = array_merge($result, Str::split($row, $split));
            } else {
                $result[] = $row;
            }
        }

        if ($unique === true) {
            $result = array_unique($result);
        }

        return array_values($result);
    }

    /**
     * Prepends an element to the data array
     *
     * @param  mixed       $key
     * @param  mixed       $item
     * @return Collection
     */
    public function prepend(...$args): self
    {
        if (count($args) === 1) {
            array_unshift($this->data, $args[0]);
        } elseif (count($args) === 2) {
            $data = $this->data;
            $this->data = [];
            $this->set($args[0], $args[1]);
            $this->data += $data;
        }

        return $this;
    }

    /**
     * Runs a combination of filterBy, sortBy, not
     * offset, limit and paginate on the collection.
     * Any part of the query is optional.
     *
     * @param array $arguments
     * @return self
     */
    public function query(array $arguments = [])
    {
        $result = clone $this;

        if (isset($arguments['not']) === true) {
            $result = $result->not(...$arguments['not']);
        }

        if (isset($arguments['filterBy']) === true) {
            foreach ($arguments['filterBy'] as $filter) {
                if (isset($filter['field']) === true && isset($filter['value']) === true) {
                    $result = $result->filterBy($filter['field'], $filter['operator'] ?? '==', $filter['value']);
                }
            }
        }

        if (isset($arguments['offset']) === true) {
            $result = $result->offset($arguments['offset']);
        }

        if (isset($arguments['limit']) === true) {
            $result = $result->limit($arguments['limit']);
        }

        if (isset($arguments['sortBy']) === true) {
            if (is_array($arguments['sortBy'])) {
                $sort = explode(' ', implode(' ', $arguments['sortBy']));
            } else {
                $sort = explode(' ', $arguments['sortBy']);
            }
            $result = $result->sortBy(...$sort);
        }

        if (isset($arguments['paginate']) === true) {
            $result = $result->paginate($arguments['paginate']);
        }

        return $result;
    }

    /**
     * Removes an element from the array by key
     *
     * @param mixed $key the name of the key
     */
    public function remove($key)
    {
        $this->__unset($key);
        return $this;
    }

    /**
     * Adds a new element to the collection
     *
     * @param  mixed  $key    string or array
     * @param  mixed  $value
     * @return self
     */
    public function set($key, $value = null): self
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->__set($k, $v);
            }
        } else {
            $this->__set($key, $value);
        }
        return $this;
    }

    /**
     * Shuffle all elements
     *
     * @return Collection
     */
    public function shuffle()
    {
        $data = $this->data;
        $keys = $this->keys();
        shuffle($keys);

        $collection = clone $this;
        $collection->data = [];

        foreach ($keys as $key) {
            $collection->data[$key] = $data[$key];
        }

        return $collection;
    }

    /**
     * Returns a slice of the object
     *
     * @param  int        $offset  The optional index to start the slice from
     * @param  int        $limit   The optional number of elements to return
     * @return Collection
     */
    public function slice(int $offset = 0, int $limit = null)
    {
        if ($offset === 0 && $limit === null) {
            return $this;
        }

        $collection = clone $this;
        $collection->data = array_slice($this->data, $offset, $limit);
        return $collection;
    }

    /**
     * Sorts the elements by any number of fields
     *
     * @param   $field      string|callable  Field name or value callback to sort by
     * @param   $direction  string           asc or desc
     * @param   $method     int              The sort flag, SORT_REGULAR, SORT_NUMERIC etc.
     * @return  Collection
     */
    public function sortBy()
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
            $currentField = $fields ? count($fields) - 1 : 0;

            // detect the type of argument
            // sorting direction
            $argLower = is_string($arg) ? strtolower($arg) : null;

            if ($arg === SORT_ASC || $argLower === 'asc') {
                $fields[$currentField]['direction'] = SORT_ASC;
            } elseif ($arg === SORT_DESC || $argLower === 'desc') {
                $fields[$currentField]['direction'] = SORT_DESC;

            // other string: the field name
            } elseif (is_string($arg) === true) {
                $values = [];

                foreach ($array as $key => $value) {
                    $value = $collection->getAttribute($value, $arg);

                    // make sure that we return something sortable
                    // but don't convert other scalars (especially numbers) to strings!
                    $values[$key] = is_scalar($value) === true ? $value : (string)$value;
                }

                $fields[] = ['field' => $arg, 'values' => $values];

            // callable: custom field values
            } elseif (is_callable($arg) === true) {
                $values = [];

                foreach ($array as $key => $value) {
                    $value = $arg($value);

                    // make sure that we return something sortable
                    // but don't convert other scalars (especially numbers) to strings!
                    $values[$key] = is_scalar($value) === true ? $value : (string)$value;
                }

                $fields[] = ['field' => null, 'values' => $values];

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
            $params[] = $field['flags']     ?? SORT_NATURAL | SORT_FLAG_CASE;
        }

        $params[] = &$array;

        // array_multisort receives $params as separate params
        array_multisort(...$params);

        // $array has been overwritten by array_multisort
        $collection->data = $array;
        return $collection;
    }

    /**
     * Converts the object into an array
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
     * Converts the object into a JSON string
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Convertes the object to a string
     *
     * @return string
     */
    public function toString(): string
    {
        return implode('<br />', $this->keys());
    }

    /**
     * Returns an non-associative array
     * with all values
     *
     * @return array
     */
    public function values(): array
    {
        return array_values($this->data);
    }

    /**
     * Alias for $this->not()
     *
     * @param  args    any number of keys, passed as individual arguments
     * @return Collection
     */
    public function without(...$keys)
    {
        return $this->not(...$keys);
    }
}

/**
 * Equals Filter
 */
Collection::$filters['=='] = function ($collection, $field, $test, $split = false) {
    foreach ($collection->data as $key => $item) {
        $value = $collection->getAttribute($item, $field, $split, $test);

        if ($split !== false) {
            if (in_array($test, $value) === false) {
                unset($collection->data[$key]);
            }
        } elseif ($value !== $test) {
            unset($collection->data[$key]);
        }
    }

    return $collection;
};

/**
 * Not Equals Filter
 */
Collection::$filters['!='] = function ($collection, $field, $test, $split = false) {
    foreach ($collection->data as $key => $item) {
        $value = $collection->getAttribute($item, $field, $split, $test);

        if ($split !== false) {
            if (in_array($test, $value) === true) {
                unset($collection->data[$key]);
            }
        } elseif ((string)$value == $test) {
            unset($collection->data[$key]);
        }
    }

    return $collection;
};

/**
 * In Filter
 */
Collection::$filters['in'] = [
    'validator' => function ($value, $test) {
        return in_array($value, $test) === true;
    },
    'strict' => false
];

/**
 * Not In Filter
 */
Collection::$filters['not in'] = [
    'validator' => function ($value, $test) {
        return in_array($value, $test) === false;
    },
];

/**
 * Contains Filter
 */
Collection::$filters['*='] = [
    'validator' => function ($value, $test) {
        return strpos($value, $test) !== false;
    },
    'strict' => false
];

/**
 * Not Contains Filter
 */
Collection::$filters['!*='] = [
    'validator' => function ($value, $test) {
        return strpos($value, $test) === false;
    },
];

/**
 * More Filter
 */
Collection::$filters['>'] = [
    'validator' => function ($value, $test) {
        return $value > $test;
    }
];

/**
 * Min Filter
 */
Collection::$filters['>='] = [
    'validator' => function ($value, $test) {
        return $value >= $test;
    }
];

/**
 * Less Filter
 */
Collection::$filters['<'] = [
    'validator' => function ($value, $test) {
        return $value < $test;
    }
];

/**
 * Max Filter
 */
Collection::$filters['<='] = [
    'validator' => function ($value, $test) {
        return $value <= $test;
    }
];

/**
 * Ends With Filter
 */
Collection::$filters['$='] = [
    'validator' => 'V::endsWith',
    'strict'    => false,
];

/**
 * Not Ends With Filter
 */
Collection::$filters['!$='] = [
    'validator' => function ($value, $test) {
        return V::endsWith($value, $test) === false;
    }
];

/**
 * Starts With Filter
 */
Collection::$filters['^='] = [
    'validator' => 'V::startsWith',
    'strict'    => false
];

/**
 * Not Starts With Filter
 */
Collection::$filters['!^='] = [
    'validator' => function ($value, $test) {
        return V::startsWith($value, $test) === false;
    }
];

/**
 * Between Filter
 */
Collection::$filters['between'] = [
    'validator' => function ($value, $test) {
        return V::between($value, ...$test) === true;
    },
    'strict' => false
];

/**
 * Match Filter
 */
Collection::$filters['*'] = [
    'validator' => 'V::match',
    'strict'    => false
];

/**
 * Not Match Filter
 */
Collection::$filters['!*'] = [
    'validator' => function ($value, $test) {
        return V::match($value, $test) === false;
    }
];

/**
 * Max Length Filter
 */
Collection::$filters['maxlength'] = [
    'validator' => 'V::maxLength',
];

/**
 * Min Length Filter
 */
Collection::$filters['minlength'] = [
    'validator' => 'V::minLength'
];

/**
 * Max Words Filter
 */
Collection::$filters['maxwords'] = [
    'validator' => 'V::maxWords',
];

/**
 * Min Words Filter
 */
Collection::$filters['minwords'] = [
    'validator' => 'V::minWords',
];

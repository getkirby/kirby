<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Collection as BaseCollection;
use Kirby\Toolkit\Str;

/**
 * The Collection class serves as foundation
 * for the Pages, Files, Users and Structure
 * classes. It handles object validation and sets
 * the parent collection property for each object.
 * The `getAttribute` method is also adjusted to
 * handle values from Field objects correctly, so
 * those can be used in filters as well.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class Collection extends BaseCollection
{
    use HasMethods;

    /**
     * Magic getter function
     *
     * @param  string $key
     * @param  mixed  $arguments
     * @return mixed
     */
    public function __call(string $key, $arguments)
    {
        // collection methods
        if ($this->hasMethod($key)) {
            return $this->callMethod($key, $arguments);
        }
    }

    /**
     * Stores the parent object, which is needed
     * in some collections to get the finder methods right.
     *
     * @var object
     */
    protected $parent;

    /**
     * Creates a new Collection with the given objects
     *
     * @param array $objects
     * @param object $parent
     */
    public function __construct($objects = [], $parent = null)
    {
        $this->parent = $parent;
        parent::__construct($objects);
    }

    /**
     * Internal setter for each object in the Collection.
     * This takes care of Component validation and of setting
     * the collection prop on each object correctly.
     *
     * @param string $id
     * @param object $object
     */
    public function __set(string $id, $object)
    {
        // inject the collection for proper navigation
        $object->collection = $this;
        $this->data[$object->id()] = $object;
    }

    /**
     * Returns the plain prop value from a given
     * object, to be used in filter functions.
     *
     * @param  object $object
     * @param  string $prop
     * @return mixed
     */
    public function getAttribute($object, $prop)
    {
        return (string)$object->$prop();
    }

    /**
     * Groups the items by a given field
     *
     * @param string $field
     * @param bool   $i (ignore upper/lowercase for group names)
     * @return Collection A collection with an item for each group and a Collection for each group
     */
    public function groupBy(string $field, bool $i = true)
    {
        $groups = new Collection([], $this->parent());

        foreach ($this->data as $key => $item) {
            $value = $this->getAttribute($item, $field);

            // make sure that there's always a proper value to group by
            if (!$value) {
                throw new InvalidArgumentException('Invalid grouping value for key: ' . $key);
            }

            // ignore upper/lowercase for group names
            if ($i) {
                $value = Str::lower($value);
            }

            if (isset($groups->data[$value]) === false) {
                // create a new entry for the group if it does not exist yet
                $groups->data[$value] = new static([$key => $item]);
            } else {
                // add the item to an existing group
                $groups->data[$value]->set($key, $item);
            }
        }

        return $groups;
    }

    /**
     * Checks if the given object or id
     * is in the collection
     *
     * @param string|object
     * @return boolean
     */
    public function has($id): bool
    {
        if (is_object($id) === true) {
            $id = $id->id();
        }

        return parent::has($id);
    }

    /**
     * Correct position detection for objects.
     * The method will automatically detect objects
     * or ids and then search accordingly.
     *
     * @param  string|object $object
     * @return int
     */
    public function indexOf($object): int
    {
        if (is_string($object) === true) {
            return array_search($object, $this->keys());
        }

        return array_search($object->id(), $this->keys());
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
            if (is_object($key) === true) {
                $key = $key->id();
            }
            unset($collection->$key);
        }
        return $collection;
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
     * Returns the parent model
     *
     * @return Model
     */
    public function parent()
    {
        return $this->parent;
    }

    /**
     * Searches the collection
     *
     * @param string $query
     * @param array $params
     * @return self
     */
    public function search(string $query = null, $params = [])
    {
        return Search::collection($this, $query, $params);
    }

    /**
     * Converts all objects in the collection
     * to an array. This can also take a callback
     * function to further modify the array result.
     *
     * @param  Closure $map
     * @return array
     */
    public function toArray(Closure $map = null): array
    {
        return parent::toArray($map ?? function ($object) {
            return $object->toArray();
        });
    }
}

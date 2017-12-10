<?php

namespace Kirby\Cms;

use Closure;
use Exception;
use Kirby\Collection\Collection as BaseCollection;

/**
 * The Collection class serves as foundation
 * for the Pages, Files, Children, Users and Structure
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

    use HasPlugins;

    /**
     * Accepts any extension of the Object class
     *
     * @var string
     */
    protected static $accept = Object::class;

    /**
     * Stores the parent object, which is needed
     * in some collections to get the finder methods
     * right. Especially in the ChildrenFinder class.
     *
     * @var Object
     */
    protected $parent;

    /**
     * Creates a new Collection with the given objects
     *
     * @param array $objects
     * @param Object $parent
     */
    public function __construct($objects = [], Object $parent = null)
    {
        $this->parent = $parent;
        parent::__construct($objects);
    }

    /**
     * Internal setter for each object in the Collection.
     * This takes care of Object validation and of setting
     * the collection prop on each object correctly.
     *
     * @param string $id
     * @param Object $object
     */
    public function __set(string $id, $object)
    {
        if (!is_a($object, static::$accept)) {
            throw new Exception(sprintf('Invalid object in collection. Accepted: "%s"', static::$accept));
        }

        // inject the collection for proper navigation
        $object->set('collection', $this);

        return parent::__set($object->id(), $object);
    }

    /**
     * Returns the plain prop value from a given
     * object, to be used in filter functions.
     *
     * @param  Object $object
     * @param  string $prop
     * @return mixed
     */
    public function getAttribute($object, $prop)
    {
        return (string)$object->$prop();
    }

    /**
     * Correct position detection for objects.
     * The method will automatically detect objects
     * or ids and then search accordingly.
     *
     * @param  string|Object $object
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
     * @return Object
     */
    public function not(...$keys)
    {
        $collection = $this->clone();
        foreach ($keys as $key) {
            if (is_a($key, Object::class)) {
                $key = $key->id();
            }
            unset($collection->$key);
        }
        return $collection;
    }

    /**
     * Add pagination
     *
     * @param  int        $limit  number of items per page
     * @param  int        $page   optional page number to return
     * @return Collection         a sliced set of data
     */
    public function paginate(...$arguments)
    {

        if (is_array($arguments[0])) {
            $options = $arguments[0];
        } else {
            $options = [
                'limit' => $arguments[0],
                'page'  => $arguments[1] ?? null,
            ];
        }

        if ($this->hasPlugin('pagination')) {
            $this->pagination = $this->plugin('pagination', [$options]);
        } else {
            $this->pagination = new Pagination([
                'total' => $this->count(),
                'limit' => $options['limit'] ?? 10,
                'page'  => $options['page']  ?? null,
            ]);
        }

        // slice and clone the collection according to the pagination
        return $this->slice($this->pagination->offset(), $this->pagination->limit());
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
        return parent::toArray($map ?? function (Object $object) {
            return $object->toArray();
        });
    }

}

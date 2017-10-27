<?php

namespace Kirby\Cms;

use Closure;
use Exception;
use Kirby\Collection\Collection as BaseCollection;

class Collection extends BaseCollection
{

    use HasPlugins;

    protected static $accept = Object::class;
    protected $parent;

    public function __construct($items = [], Object $parent = null)
    {
        $this->parent = $parent;
        parent::__construct($items);
    }

    public function indexOf($object)
    {
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

    public function getAttribute($object, $prop)
    {
        return (string)$object->$prop();
    }

    public function __set(string $id, $object)
    {

        if (!is_a($object, static::$accept)) {
            throw new Exception(sprintf('Invalid "%s" object in collection', static::$accept));
        }

        // inject the collection for proper navigation
        $object->set('collection', $this);

        return parent::__set($object->id(), $object);

    }

    public function toArray(Closure $map = null): array
    {
        return parent::toArray($map ?? function (Object $object) {
            return $object->toArray();
        });
    }

}

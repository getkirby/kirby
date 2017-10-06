<?php

namespace Kirby\Collection\Traits;

trait Mutator
{

    public function clone()
    {
        $clone = clone $this;
        return $clone;
    }

    /**
     * Getter and setter for the collection data
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
     * Clone and remove all items from the collection
     *
     * @return Collection
     */
    public function empty()
    {
        $collection = clone $this;
        return $collection->data([]);
    }

    /**
     * Low-level setter for collection items
     *
     * @param string  $key    string or array
     * @param mixed   $value
     */
    public function __set(string $key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Adds a new item to the collection
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
     * Appends an element to the data array
     *
     * @param  mixed      $key
     * @param  mixed      $item
     * @return Object
     */
    public function append($key, $item): self
    {
        return $this->set($key, $item);
    }

    /**
     * Prepends an element to the data array
     *
     * @param  mixed       $key
     * @param  mixed       $item
     * @return Collection
     */
    public function prepend($key, $item): self
    {
        $data = $this->data;

        $this->data = [];
        $this->set($key, $item);
        $this->data += $data;

        return $this;
    }

    /**
     * Adds all items to the collection
     *
     * @return Collection
     */
    public function extend($items): self
    {
        $collection = clone $this;
        return $collection->set($items);
    }

    /**
     * Low-level item remover
     *
     * @param mixed $key the name of the key
     */
    public function __unset($key)
    {
        unset($this->data[$key]);
    }

    /**
     * Removes an element from the array by key
     *
     * @param mixed $key the name of the key
     */
    public function remove($key): self
    {
        $this->__unset($key);
        return $this;
    }

    /**
     * Map a function to each item in the collection
     *
     * @param  callable $callback
     * @return Collection
     */
    public function map(callable $callback): self
    {
        $this->data = array_map($callback, $this->data);
        return $this;
    }

}

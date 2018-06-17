<?php

namespace Kirby\Toolkit;

/**
 * Extended version of PHP's iterator
 * class that builds the foundation of our
 * Collection and Stack classes.
 */
class Iterator implements \Iterator
{

    /**
     * The data array
     *
     * @var array
     */
    protected $data = [];

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Returns the current key from the array
     *
     * @return string
     */
    public function key()
    {
        return key($this->data);
    }

    /**
     * Returns an array of all keys in the Iterator
     *
     * @return array
     */
    public function keys(): array
    {
        return array_keys($this->data);
    }

    /**
     * Returns the current element of the array
     *
     * @return mixed
     */
    public function current()
    {
        return current($this->data);
    }

    /**
     * Moves the cursor to the previous element in the array
     * and returns it
     *
     * @return mixed
     */
    public function prev()
    {
        return prev($this->data);
    }

    /**
     * Moves the cursor to the next element in the array
     * and returns it
     *
     * @return mixed
     */
    public function next()
    {
        return next($this->data);
    }

    /**
     * Moves the cusor to the first element of the array
     */
    public function rewind()
    {
        reset($this->data);
    }

    /**
     * Checks if the current element is valid
     *
     * @return boolean
     */
    public function valid(): bool
    {
        return $this->current() !== false;
    }

    /**
     * Counts all elements in the array
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Tries to find the index number for the given element
     *
     * @param  mixed         $needle  the element to search for
     * @return string|false           the name of the key or false
     */
    public function indexOf($needle)
    {
        return array_search($needle, array_values($this->data));
    }

    /**
     * Tries to find the key for the given element
     *
     * @param  mixed         $needle  the element to search for
     * @return string|false           the name of the key or false
     */
    public function keyOf($needle)
    {
        return array_search($needle, $this->data);
    }

    /**
     * Checks if an element is in the collection by key.
     *
     * @param  mixed  $key
     * @return boolean
     */
    public function has($key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * Checks if the current key is set
     *
     * @param  mixed  $key  the key to check
     * @return boolean
     */
    public function __isset($key): bool
    {
        return $this->has($key);
    }

    /**
     * Simplified var_dump output
     *
     * @return array
     */
    public function __debuginfo(): array
    {
        return $this->data;
    }
}

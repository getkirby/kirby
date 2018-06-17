<?php

namespace Kirby\Toolkit;

/**
 * A wrapper around simple arrays with
 * a much nicer chainable API for advanced
 * sorting, manipulation and navigation.
 */
class Stack extends Iterator
{
    public function __construct(array $items)
    {
        $this->data = array_values($items);
    }

    public function append(...$items)
    {
        array_push($this->data, ...$items);
        return $this;
    }

    public function first()
    {
        return $this->data[0] ?? null;
    }

    public function indexOf($needle)
    {
        return array_search($needle, $this->data);
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    public function isEven(): bool
    {
        return $this->count() % 2 === 0;
    }

    public function isNotEmpty(): bool
    {
        return $this->count() !== 0;
    }

    public function isOdd(): bool
    {
        return $this->count() % 2 !== 0;
    }

    public function last()
    {
        return $this->data[count($this->data) - 1] ?? null;
    }

    /**
     * Returns a new object with a limited number of elements
     *
     * @param int $limit The number of elements to return
     * @return self
     */
    public function limit(int $limit): self
    {
        return $this->slice(0, $limit);
    }

    /**
     * Map a function to each item in the collection
     *
     * @param callable $callback
     * @return self
     */
    public function map(callable $callback): self
    {
        $this->data = array_map($callback, $this->data);
        return $this;
    }

    public function nth(int $n)
    {
        return $this->data[$n] ?? null;
    }

    /**
     * Returns a new object starting from the given offset
     *
     * @param  int $offset The index to start from
     * @return self
     */
    public function offset(int $offset): self
    {
        return $this->slice($offset);
    }

    public function prepend(...$items)
    {
        array_unshift($this->data, ...$items);
        return $this;
    }

    public function remove(...$items)
    {
        foreach ($items as $item) {
            unset($this->data[$this->indexOf($item)]);
        }
        return $this;
    }

    /**
     * Returns a slice of the object
     *
     * @param  int        $offset  The optional index to start the slice from
     * @param  int        $limit   The optional number of elements to return
     * @return Collection
     */
    public function slice(int $offset = 0, int $limit = null): self
    {
        if ($offset === 0 && $limit === null) {
            return $this;
        }

        $this->data = array_slice($this->data, $offset, $limit);
        return $this;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function toJson(...$arguments): string
    {
        return json_encode($this->data, ...$arguments);
    }
}

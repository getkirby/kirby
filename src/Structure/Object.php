<?php

namespace Kirby\Structure;

use Kirby\Fields\Field;
use Kirby\Fields\Fields;

class Object
{

    protected $attributes;

    public function __construct(array $attributes = [], array $dependencies = [])
    {
        $this->attributes = new Fields($attributes, function ($key, $value) use ($dependencies) {
            return new Field($key, $value, $dependencies);
        });
    }

    public function __call(string $method, array $arguments = [])
    {
        return $this->attributes->get($method);
    }

    public function collection(Collection $collection = null)
    {
        if ($collection === null) {
            return $this->collection;
        }

        $this->collection = $collection;
        return $this;
    }

    public function indexOf()
    {
        return $this->collection()->indexOf($this);
    }

    public function prev()
    {
        return $this->collection()->nth($this->indexOf() - 1);
    }

    public function hasPrev(): bool
    {
        return $this->prev() !== null;
    }

    public function next()
    {
        return $this->collection()->nth($this->indexOf() + 1);
    }

    public function hasNext(): bool
    {
        return $this->next() !== null;
    }

    public function isFirst(): bool
    {
        return $this->collection()->first()->is($this);
    }

    public function isLast(): bool
    {
        return $this->collection()->last()->is($this);
    }

    public function toArray(): array
    {
        return $this->attributes->toArray();
    }

}

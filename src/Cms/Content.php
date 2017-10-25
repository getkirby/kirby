<?php

namespace Kirby\Cms;

use Closure;
use Exception;

class Content
{
    protected $data = [];
    protected $fields = [];
    protected $parent;

    public function __construct($data = [], Object $parent = null)
    {
        $this->data = $data;
        $this->parent = $parent;
    }

    public function data()
    {
        return $this->data;
    }

    public function keys()
    {
        return array_keys($this->data());
    }

    public function get(string $key = null)
    {
        if ($key === null) {
            $data = [];
            foreach ($this->data() as $key => $value) {
                $data[] = $this->get($key);
            }
            return $data;
        }

        $key = strtolower($key);

        if (isset($this->fields[$key])) {
            return $this->fields[$key];
        }

        return $this->fields[$key] = new Field($key, $this->data()[$key] ?? null, $this->parent);

    }

    public function fields(): array
    {
        foreach ($this->data as $key => $value) {
            $this->get($key);
        }
        return $this->fields;
    }

    public function not(...$keys): self
    {

        $copy = clone $this;
        $copy->fields = null;

        foreach ($keys as $key) {
            unset($copy->data[$key]);
        }

        return $copy;

    }

    public function toArray(): array
    {
        return $this->data();
    }

    public function update(array $content = []): self
    {
        $this->data = array_merge($this->data, $content);
        return $this;
    }

    public function __debuginfo(): array
    {
        return $this->toArray();
    }

}

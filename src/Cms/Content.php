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

    public function get(string $key = null, array $arguments = [])
    {
        if ($key === null) {
            return $this->fields();
        }

        $key = strtolower($key);

        if (isset($this->fields[$key])) {
            return $this->fields[$key];
        }

        $this->fields[$key] = new Field($key, $this->data()[$key] ?? null, $this->parent);

        // field method shortcuts
        switch ($key) {
            case 'date':
                // don't use the date field
                if (empty($arguments[1]) === false && $arguments[1] !== 'date') {
                    return $this->get($arguments[1])->toDate(...$arguments);
                }
                return $this->fields[$key]->toDate(...$arguments);
                break;
            default:
                return $this->fields[$key];
        }


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

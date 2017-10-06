<?php

namespace Kirby\Fields;

use Closure;
use Exception;

class Fields
{
    protected $field;
    protected $data   = [];
    protected $fields = [];

    public function __construct($data = [], $field = null)
    {
        $this->data  = $data;
        $this->field = $field ?? function($key, $value) {
            return $value;
        };
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

        return $this->fields[$key] = call_user_func(
            $this->field,
            $key,
            $this->data()[$key] ?? null
        );
    }

    public function fields(): array
    {
        foreach ($this->data as $key => $value) {
            $this->get($key);
        }
        return $this->fields;
    }

    public function toArray(): array
    {
        return $this->data();
    }

    public function __debuginfo(): array
    {
        return $this->toArray();
    }

}

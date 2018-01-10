<?php

namespace Kirby\Cms;

use Exception;

class Fields
{

    protected $fields;
    protected $values;
    protected $parent;

    public function __construct(array $fields = [], array $values = [], $parent = null)
    {
        $this->parent = $parent;
        $this->values = $values;

        foreach ($fields as $name => $field) {
            $field['name']  = $name = $field['name'] ?? $name;
            $field['model'] = $parent;

            if (isset($values[$name]) === true) {
                $field['value'] = $values[$name];
            }

            $this->fields[$name] = new Field($field['type'], $field);
        }
    }

    public function errors(): array
    {
        $errors = [];

        foreach ($this->fields as $name => $field) {
            $errors[$name] = $field->error() ?: null;
        }

        return $errors;
    }

    public function names(): array
    {
        return array_keys($this->fields);
    }

    public function submit(array $input): array
    {
        $result = [];

        foreach ($this->fields as $name => $field) {
            $result[$name] = $field->submit($input[$name] ?? null);
        }

        return $result;
    }

    public function toArray(): array
    {
        return array_map(function ($field) {
            return $field->toArray();
        }, $this->fields);
    }

    public function validate(array $input = []): bool
    {
        $result = true;

        foreach ($this->fields as $name => $field) {
            try {
                if ($field->validate($input[$name] ?? $field->value()) !== true) {
                    $result = false;
                }
            } catch (Exception $e) {
                $result = false;
            }
        }

        return $result;
    }

    public function values(array $values = null): array
    {
        if ($values !== null) {
            foreach ($values as $key => $value) {
                $this->fields[$key]->value = $value;
            }
        }

        $values = [];

        foreach ($this->fields as $name => $field) {
            $values[$name] = $field->value();
        }

        return $values;
    }

    public function createTextValues(array $input = null): array
    {
        $values = [];

        foreach ($this->fields as $name => $field) {
            $values[$name] = $field->createTextValue($input[$name] ?? $field->default());
        }

        return $values;
    }

}

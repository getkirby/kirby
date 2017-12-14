<?php

namespace Kirby\Panel;

use Exception;

class BlueprintSection extends BlueprintObject
{
    protected $fields = null;

    public function schema(): array
    {
        return [
            'fields' => [
                'type' => 'array'
            ],
            'name' => [
                'type'     => 'string',
                'required' => true
            ],
            'type' => [
                'type'     => 'string',
                'required' => true,
            ],
        ];
    }

    public function fields(): array
    {
        if (is_array($this->fields) === true) {
            return $this->fields;
        }

        $this->fields = [];

        foreach ((array)$this->prop('fields') as $field) {
            if (is_array($field) === false) {
                throw new Exception('Fields must be defined as array');
            }
            $field = new BlueprintField($field);
            $this->fields[$field->name()] = $field;
        }

        return $this->fields;
    }

    public function field(string $name)
    {
        return $this->fields[$name] ?? null;
    }

    public function toArray(): array
    {
        $fields = array_map(function ($field) {
            return $field->toArray();
        }, $this->fields());

        return array_merge(parent::toArray(), [
            'fields' => $fields
        ]);
    }
}

<?php

namespace Kirby\Cms;

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
            'id' => [
                'type'    => 'string',
                'default' => function () {
                    return $this->name();
                }
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

    public function fields(): BlueprintCollection
    {
        if (is_a($this->fields, BlueprintCollection::class) === true) {
            return $this->fields;
        }

        $this->fields = new BlueprintCollection;

        foreach ((array)$this->prop('fields') as $props) {
            $field = new BlueprintField($props);
            $this->fields->set($field->name(), $field);
        }

        return $this->fields;
    }

    public function field(string $name)
    {
        return $this->fields()->find($name);
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['fields'] = $this->fields()->toArray();

        return $array;
    }

    public function toLayout()
    {
        return $this->id();
    }

}

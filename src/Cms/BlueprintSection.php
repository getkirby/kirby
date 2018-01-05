<?php

namespace Kirby\Cms;

use Exception;

class BlueprintSection extends BlueprintObject
{
    protected $fields = null;

    public function schema(): array
    {
        return [
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

        foreach ((array)$this->props->fields as $name => $props) {
            // use the key as name if the name is not set
            $props['name'] = $props['name'] ?? $name;
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
        $array = $this->props->not('fields', 'collection')->toArray();

        if ($this->type() === 'fields') {
            $array['fields'] = $this->fields()->toArray();
        }

        ksort($array);
        return $array;
    }

    public function toLayout()
    {
        return $this->id();
    }

}
